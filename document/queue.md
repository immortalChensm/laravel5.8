### **队列系统** 
 [首页](../readme.md)   
 [什么是队列](https://learnku.com/articles/30430)     

- 队列任务创建  
    php artisan make:job xxx   
    Illuminate\Foundation\Providers\ArtisanServiceProvider
    
    输入的命令触发的内容   
    ```php  
      protected function registerJobMakeCommand()
        {
            $this->app->singleton('command.job.make', function ($app) {
                return new JobMakeCommand($app['files']);
            });
        }
    ```  
    Illuminate\Foundation\Console\JobMakeCommand  
    
    框架的命令类【有的放Symfony组件下，有的放Illuminate目录下，反正它就是随便放，其实你也可以使用整！】  
    看完console的说明你也应该能随便写个所谓的命令类了【废话】   
    
    ```php  
     public function handle()
        {
        //得到输入的job 任务名称类
            $name = $this->qualifyClass($this->getNameInput());
            //拼装构成任务类文件
            $path = $this->getPath($name);
            //创建任务类目录
            $this->makeDirectory($path);
            //生成任务类文件
            $this->files->put($path, $this->buildClass($name));
    
            $this->info($this->type.' created successfully.');
        }
        
     protected function buildClass($name)
         {
         //得到任务类模板内容
             $stub = $this->files->get($this->getStub());
              //用具体的任务名称替换模板内容
             return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
         }
         
     protected function getStub()
         {
         //得到任务类文件模板内容
             return $this->option('sync')
                             ? __DIR__.'/stubs/job.stub'
                             : __DIR__.'/stubs/job-queued.stub';
         }
    ```  
    
    任务模板内容  
    ```php  
    <?php
    
    namespace DummyNamespace;
    
    use Illuminate\Bus\Queueable;
    use Illuminate\Foundation\Bus\Dispatchable;
    
    class DummyClass
    {
        use Dispatchable, Queueable;
    
        /**
         * Create a new job instance.
         *
         * @return void
         */
        public function __construct()
        {
            //
        }
    
        /**
         * Execute the job.
         *
         * @return void
         */
        public function handle()
        {
            //
        }
    }
    <?php
    
    namespace DummyNamespace;
    
    use Illuminate\Bus\Queueable;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    
    class DummyClass implements ShouldQueue
    {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
        /**
         * Create a new job instance.
         *
         * @return void
         */
        public function __construct()
        {
            //
        }
    
        /**
         * Execute the job.
         *
         * @return void
         */
        public function handle()
        {
            //
        }
    }

    ```     
    
    运行后生成一个任务类,替换后内容如下    
    
    ```php  
    <?php
    
    namespace App\Jobs;
    
    use Illuminate\Bus\Queueable;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    
    class Test implements ShouldQueue
    {
    //继承的类
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
        /**
         * Create a new job instance.
         *
         * @return void
         */
        public function __construct()
        {
            //
        }
    
        /**
         * Execute the job.
         *
         * @return void
         */
        public function handle()
        {
            //
        }
    }

    ```  
    
    运行时，先运行队列服务提供类，下面是服务提供类的注册内容  
    ```php  
    <?php
    
    namespace Illuminate\Queue;
    
    use Illuminate\Support\Str;
    use Opis\Closure\SerializableClosure;
    use Illuminate\Support\ServiceProvider;
    use Illuminate\Queue\Connectors\SqsConnector;
    use Illuminate\Queue\Connectors\NullConnector;
    use Illuminate\Queue\Connectors\SyncConnector;
    use Illuminate\Queue\Connectors\RedisConnector;
    use Illuminate\Contracts\Debug\ExceptionHandler;
    use Illuminate\Queue\Connectors\DatabaseConnector;
    use Illuminate\Queue\Failed\NullFailedJobProvider;
    use Illuminate\Contracts\Support\DeferrableProvider;
    use Illuminate\Queue\Connectors\BeanstalkdConnector;
    use Illuminate\Queue\Failed\DatabaseFailedJobProvider;
    
    class QueueServiceProvider extends ServiceProvider implements DeferrableProvider
    {
        /**
         * Register the service provider.
         *
         * @return void
         */
        public function register()
        {
            $this->registerManager();
            $this->registerConnection();
            $this->registerWorker();
            $this->registerListener();
            $this->registerFailedJobServices();
            $this->registerOpisSecurityKey();
        }
    
        /**
         * Register the queue manager.
         *
         * @return void
         */
        protected function registerManager()
        {
            $this->app->singleton('queue', function ($app) {
                // Once we have an instance of the queue manager, we will register the various
                // resolvers for the queue connectors. These connectors are responsible for
                // creating the classes that accept queue configs and instantiate queues.
                return tap(new QueueManager($app), function ($manager) {
                    $this->registerConnectors($manager);
                });
            });
        }
    
        /**
         * Register the default queue connection binding.
         *
         * @return void
         */
        protected function registerConnection()
        {
            $this->app->singleton('queue.connection', function ($app) {
                return $app['queue']->connection();
            });
        }
    
        /**
         * Register the connectors on the queue manager.
         *
         * @param  \Illuminate\Queue\QueueManager  $manager
         * @return void
         */
        public function registerConnectors($manager)
        {
            foreach (['Null', 'Sync', 'Database', 'Redis', 'Beanstalkd', 'Sqs'] as $connector) {
                $this->{"register{$connector}Connector"}($manager);
            }
        }
    
        /**
         * Register the Null queue connector.
         *
         * @param  \Illuminate\Queue\QueueManager  $manager
         * @return void
         */
        protected function registerNullConnector($manager)
        {
            $manager->addConnector('null', function () {
                return new NullConnector;
            });
        }
    
        /**
         * Register the Sync queue connector.
         *
         * @param  \Illuminate\Queue\QueueManager  $manager
         * @return void
         */
        protected function registerSyncConnector($manager)
        {
            $manager->addConnector('sync', function () {
                return new SyncConnector;
            });
        }
    
        /**
         * Register the database queue connector.
         *
         * @param  \Illuminate\Queue\QueueManager  $manager
         * @return void
         */
        protected function registerDatabaseConnector($manager)
        {
            $manager->addConnector('database', function () {
                return new DatabaseConnector($this->app['db']);
            });
        }
    
        /**
         * Register the Redis queue connector.
         *
         * @param  \Illuminate\Queue\QueueManager  $manager
         * @return void
         */
        protected function registerRedisConnector($manager)
        {
            $manager->addConnector('redis', function () {
                return new RedisConnector($this->app['redis']);
            });
        }
    
        /**
         * Register the Beanstalkd queue connector.
         *
         * @param  \Illuminate\Queue\QueueManager  $manager
         * @return void
         */
        protected function registerBeanstalkdConnector($manager)
        {
            $manager->addConnector('beanstalkd', function () {
                return new BeanstalkdConnector;
            });
        }
    
        /**
         * Register the Amazon SQS queue connector.
         *
         * @param  \Illuminate\Queue\QueueManager  $manager
         * @return void
         */
        protected function registerSqsConnector($manager)
        {
            $manager->addConnector('sqs', function () {
                return new SqsConnector;
            });
        }
    
        /**
         * Register the queue worker.
         *
         * @return void
         */
        protected function registerWorker()
        {
            $this->app->singleton('queue.worker', function () {
                return new Worker(
                    $this->app['queue'], $this->app['events'], $this->app[ExceptionHandler::class]
                );
            });
        }
    
        /**
         * Register the queue listener.
         *
         * @return void
         */
        protected function registerListener()
        {
            $this->app->singleton('queue.listener', function () {
                return new Listener($this->app->basePath());
            });
        }
    
        /**
         * Register the failed job services.
         *
         * @return void
         */
        protected function registerFailedJobServices()
        {
            $this->app->singleton('queue.failer', function () {
                $config = $this->app['config']['queue.failed'];
    
                return isset($config['table'])
                            ? $this->databaseFailedJobProvider($config)
                            : new NullFailedJobProvider;
            });
        }
    
        /**
         * Create a new database failed job provider.
         *
         * @param  array  $config
         * @return \Illuminate\Queue\Failed\DatabaseFailedJobProvider
         */
        protected function databaseFailedJobProvider($config)
        {
            return new DatabaseFailedJobProvider(
                $this->app['db'], $config['database'], $config['table']
            );
        }
    
        /**
         * Configure Opis Closure signing for security.
         *
         * @return void
         */
        protected function registerOpisSecurityKey()
        {
            if (Str::startsWith($key = $this->app['config']->get('app.key'), 'base64:')) {
                $key = base64_decode(substr($key, 7));
            }
    
            SerializableClosure::setSecretKey($key);
        }
    
        /**
         * Get the services provided by the provider.
         *
         * @return array
         */
        public function provides()
        {
            return [
                'queue', 'queue.worker', 'queue.listener',
                'queue.failer', 'queue.connection',
            ];
        }
    }
 
    
    ```   
        