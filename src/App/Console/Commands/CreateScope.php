<?php

namespace LaravelCommon\App\Console\Commands;

use Illuminate\Console\Command;
use LaravelCommon\App\Repositories\ScopeRepository;
use LaravelCommon\Utilities\Database\UnitOfWork;

class CreateScope extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'common:data:create-scope  
    {name : Name of scope}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new scope';

    /**
     * Undocumented variable
     *
     * @var ScopeRepository
     */
    protected ScopeRepository $scopeRepository;

    /**
     * Undocumented variable
     *
     * @var UnitOfWork
     */
    protected UnitOfWork $unitOfWork;

    /**
     * Create a new command instance.
     *
     * @param ScopeRepository $scopeRepository
     * @param UnitOfWork $unitOfWork
     */
    public function __construct(
        ScopeRepository $scopeRepository,
        UnitOfWork $unitOfWork
    ) {
        $this->scopeRepository = $scopeRepository;
        $this->unitOfWork = $unitOfWork;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');
        $scope = $this->scopeRepository->newEntity();
        $scope->setName($name);
        $this->unitOfWork->persist($scope);
        $this->unitOfWork->flush();
        $this->info('Scope created');
        return 0;
    }
}
