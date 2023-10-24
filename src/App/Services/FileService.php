<?php

namespace LaravelCommon\App\Services;

use DateTime;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use LaravelCommon\App\Models\_Reserved\File;

class FileService
{
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
        foreach ($fileTypes as $fileType) {
            $this->fileTypes[] = strtolower($fileType);
        }
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
        $fileType = $uploadedFile->getClientOriginalExtension();

        if (
            !empty($this->fileTypes) &&
            !in_array(strtolower($fileType), $this->fileTypes)
        ) {
            throw new Exception("file type of '$fileType' is not allowed");
        }

        $dateTime = new DateTime();
        $year = $dateTime->format('Y');
        $month = $dateTime->format('m');

        $path .= "$year/$month";

        $path = $uploadedFile->store($path);

        if (is_bool($path)) {
            throw new Exception("Failed to move file");
        }

        $extension = $uploadedFile->getClientOriginalExtension();
        $size = $uploadedFile->getSize();
        $type = $uploadedFile->getMimeType();

        $file = new File();
        $file->setName($path);
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
        foreach ($this->files as $file) {
            Storage::delete($file->getName());
        }
    }
}
