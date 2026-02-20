<?php

namespace LaravelCommon\App\Services;

use DateTime;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelCommon\App\Models\_Reserved\File;
use LaravelCommon\App\Trait\TenantPathCreator;

class FileService
{
    use TenantPathCreator;

    /**
     * Undocumented variable
     *
     * @var File[]
     */
    protected array $files = [];

    /**
     * Undocumented variable
     *
     * @var boolean
     */
    protected bool $timed = true;

    /**
     * Undocumented variable
     *
     * @var boolean
     */
    protected bool $hashedName = false;

    /**
     * Undocumented variable
     *
     * @var array
     */
    protected array $fileTypes = [];

    /**
     * Filesystem disk used for upload and delete.
     *
     * @var string|null
     */
    protected ?string $disk = null;

    /**
     * Use-timed will auto add prefix datetime name of your file name.
     *
     * @param boolean $useTimed
     * @return FileService
     */
    public function useTimed(bool $useTimed = true): FileService
    {
        $this->timed = $useTimed;
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param boolean $useHashedName
     * @return FileService
     */
    public function useHashedName(bool $useHashedName = true): FileService
    {
        $this->hashedName = $useHashedName;
        return $this;
    }

    /**
     * Filter allow type of file that are allowed to be uploaded
     *
     * @param array $fileTypes
     * @return FileService
     */
    public function allowedFileTypes(array $fileTypes = []): FileService
    {
        $this->fileTypes = array_map(fn($fileType) => strtolower($fileType), $fileTypes);
        return $this;
    }

    /**
     * Set filesystem disk name explicitly.
     *
     * @param string $disk
     * @return FileService
     */
    public function useDisk(string $disk): FileService
    {
        $this->disk = $disk;
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param UploadedFile $uploadedFile
     * @param string $name
     * @throws Exception
     * @return File
     */
    public function upload(UploadedFile $uploadedFile, string $path): File
    {
        if (!$uploadedFile->isValid()) {
            throw new Exception($uploadedFile->getErrorMessage());
        }

        $fileType = $uploadedFile->getClientOriginalExtension();

        if (
            !empty($this->fileTypes) &&
            !in_array(strtolower($fileType), $this->fileTypes)
        ) {
            throw new Exception("file type of '$fileType' is not allowed");
        }

        $storage = Storage::disk($this->getDisk());
        $uploadPath = $this->resolveUploadPath($path);
        $fileName = $this->resolveUploadFileName($uploadedFile);
        $targetPath = $this->buildTargetPath($uploadPath, $fileName);

        $stream = fopen($uploadedFile->getPathname(), 'rb');
        if ($stream === false) {
            throw new Exception('Failed to open uploaded file stream');
        }

        $result = $storage->writeStream($targetPath, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        $path = $result === false ? false : $targetPath;

        if (!$path) {
            throw new Exception(
                sprintf(
                    "Failed to move file to disk '%s' with target '%s'",
                    $this->getDisk(),
                    $targetPath ?? 'unknown'
                )
            );
        }

        $extension = $uploadedFile->getClientOriginalExtension();
        $size = $uploadedFile->getSize();
        $type = $uploadedFile->getMimeType();
        $storedPath = $this->resolveStoredPath($storage, $path);

        $file = new File();
        $file->setName($storedPath);
        $file->setOriginalName($uploadedFile->getClientOriginalName());
        $file->setExtension($extension);
        $file->setMimeType($type);
        $file->setSize($size);
        $this->addFile($file);
        return $file;
    }

    /**
     * Undocumented function
     *
     * @param UploadedFile[] $uploadedFile
     * @param string $path
     * @return FileService
     */
    public function uploadBatch($uploadedFiles, string $path): FileService
    {
        foreach ($uploadedFiles as $uploadedFile) {
            $this->upload($uploadedFile, $path);
        }
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param File $file
     * @return FileService
     */
    private function addFile(File $file): FileService
    {
        $this->files[] = $file;
        return $this;
    }

    /**
     * Get file that's been uploaded
     *
     * @return File[]
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Unlink files
     *
     * @return void
     */
    public function unlinkFiles()
    {
        $storage = Storage::disk($this->getDisk());
        foreach ($this->files as $file) {
            $storage->delete($this->normalizePathForDisk($file->getName()));
        }
    }

    public function unlink($path): bool
    {
        return Storage::disk($this->getDisk())->delete($this->normalizePathForDisk($path));
    }

    /**
     * Unlink a path by trying multiple disks and possible path formats.
     *
     * @param string $path
     * @param array|null $disks
     * @return bool
     */
    public function unlinkFromAvailableDisks(string $path, ?array $disks = null): bool
    {
        $candidateDisks = $disks ?? [
            config('filesystems.default'),
            's3',
            'local',
            'public'
        ];
        $candidateDisks = array_values(array_unique(array_filter($candidateDisks)));

        foreach ($candidateDisks as $disk) {
            foreach ($this->resolveCandidatePathsForDisk($path, $disk) as $candidatePath) {
                if ($this->useDisk($disk)->unlink($candidatePath)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get active filesystem disk.
     *
     * @return string
     */
    private function getDisk(): string
    {
        return $this->disk ?? config('filesystems.default', 'local');
    }

    /**
     * Resolve upload path based on active filesystem driver.
     *
     * Local/public keeps legacy tenant + year/month path.
     * S3 keeps only provided path (no forced local-style directory).
     *
     * @param string $path
     * @return string
     */
    private function resolveUploadPath(string $path): string
    {
        $normalizedPath = trim($path, '/');
        if ($this->isS3Disk()) {
            return $normalizedPath === '' ? '' : Str::slug($normalizedPath);
        }

        $dateTime = new DateTime();
        $year = $dateTime->format('Y');
        $month = $dateTime->format('m');
        $tenantPath = trim($this->buildTenantPath() ?? '', '/');

        return implode(
            '/',
            array_filter(['public', $tenantPath, $normalizedPath, $year, $month], fn($segment) => $segment !== '')
        );
    }

    /**
     * Determine whether active filesystem driver is s3.
     *
     * @return bool
     */
    private function isS3Disk(): bool
    {
        $driver = config('filesystems.disks.' . $this->getDisk() . '.driver');
        return $driver === 's3';
    }

    /**
     * Build final upload key.
     *
     * S3 key is flat (no slash path separator).
     *
     * @param string $uploadPath
     * @param string $fileName
     * @return string
     */
    private function buildTargetPath(string $uploadPath, string $fileName): string
    {
        if ($this->isS3Disk()) {
            return trim(($uploadPath !== '' ? $uploadPath . '-' : '') . $fileName, '-');
        }

        return trim($uploadPath . '/' . $fileName, '/');
    }

    /**
     * Build upload filename. S3 uses slug to avoid unsafe object key names.
     *
     * @param UploadedFile $uploadedFile
     * @return string
     */
    private function resolveUploadFileName(UploadedFile $uploadedFile): string
    {
        if ($this->hashedName) {
            $nameWithoutExtension = pathinfo($uploadedFile->hashName(), PATHINFO_FILENAME);
            $extension = $uploadedFile->getClientOriginalExtension();
        } else {
            $nameWithoutExtension = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $uploadedFile->getClientOriginalExtension();
        }

        if ($this->timed) {
            $nameWithoutExtension = (new DateTime())->format('YmdHis') . '-' . $nameWithoutExtension;
        }

        if ($this->isS3Disk()) {
            $nameWithoutExtension = Str::slug($nameWithoutExtension);
            if ($nameWithoutExtension === '') {
                $nameWithoutExtension = 'file';
            }
        }

        if ($extension === '') {
            return $nameWithoutExtension;
        }

        return $nameWithoutExtension . '.' . strtolower($extension);
    }

    /**
     * Return stored path format based on active disk.
     *
     * @param mixed $storage
     * @param string $path
     * @return string
     */
    private function resolveStoredPath($storage, string $path): string
    {
        if (!$this->isS3Disk()) {
            return $path;
        }

        return $storage->url($path);
    }

    /**
     * Normalize provided path/url to disk key for delete operation.
     *
     * @param string $path
     * @return string
     */
    private function normalizePathForDisk(string $path): string
    {
        if (!$this->isS3Disk()) {
            return $path;
        }

        if (!Str::startsWith($path, ['http://', 'https://'])) {
            return ltrim($path, '/');
        }

        $parsedPath = parse_url($path, PHP_URL_PATH);
        if (!is_string($parsedPath)) {
            return $path;
        }

        $normalized = ltrim($parsedPath, '/');
        $bucket = trim((string) config('filesystems.disks.' . $this->getDisk() . '.bucket'), '/');

        if ($bucket !== '' && Str::startsWith($normalized, $bucket . '/')) {
            return substr($normalized, strlen($bucket . '/'));
        }

        return $normalized;
    }

    /**
     * Resolve possible path variants for a given disk.
     *
     * @param string $path
     * @param string $disk
     * @return array
     */
    private function resolveCandidatePathsForDisk(string $path, string $disk): array
    {
        $candidates = [];

        $trimmedPath = ltrim($path, '/');
        if ($path !== '') {
            $candidates[] = $path;
        }
        if ($trimmedPath !== '') {
            $candidates[] = $trimmedPath;
        }

        if ($disk === 'public' && str_starts_with($trimmedPath, 'public/')) {
            $candidates[] = substr($trimmedPath, strlen('public/'));
        }

        $parsedPath = parse_url($path, PHP_URL_PATH);
        if (is_string($parsedPath) && $parsedPath !== '') {
            $normalizedParsedPath = ltrim($parsedPath, '/');
            if ($normalizedParsedPath !== '') {
                $candidates[] = $normalizedParsedPath;
            }

            if ($disk === 'public' && str_starts_with($normalizedParsedPath, 'public/')) {
                $candidates[] = substr($normalizedParsedPath, strlen('public/'));
            }
        }

        return array_values(array_unique(array_filter($candidates)));
    }
}
