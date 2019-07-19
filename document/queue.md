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
    Bus服务提供类  
    
    ```php  
    <?php
    
    namespace Illuminate\Bus;
    
    use Illuminate\Support\ServiceProvider;
    use Illuminate\Contracts\Support\DeferrableProvider;
    use Illuminate\Contracts\Bus\Dispatcher as DispatcherContract;
    use Illuminate\Contracts\Queue\Factory as QueueFactoryContract;
    use Illuminate\Contracts\Bus\QueueingDispatcher as QueueingDispatcherContract;
    
    class BusServiceProvider extends ServiceProvider implements DeferrableProvider
    {
        /**
         * Register the service provider.
         *
         * @return void
         */
        public function register()
        {
            $this->app->singleton(Dispatcher::class, function ($app) {
                return new Dispatcher($app, function ($connection = null) use ($app) {
                    return $app[QueueFactoryContract::class]->connection($connection);
                });
            });
    
            $this->app->alias(
                Dispatcher::class, DispatcherContract::class
            );
    
            $this->app->alias(
                Dispatcher::class, QueueingDispatcherContract::class
            );
        }
    
        /**
         * Get the services provided by the provider.
         *
         * @return array
         */
        public function provides()
        {
            return [
                Dispatcher::class,
                DispatcherContract::class,
                QueueingDispatcherContract::class,
            ];
        }
    }

    ```  
    
    任务分发  
    ```php  
    <?php
    
    namespace Illuminate\Foundation\Bus;
    
    use Illuminate\Contracts\Bus\Dispatcher;
    
    trait Dispatchable
    {
        public static function dispatch()
        {
        //new static(...func_get_args()) 将会实例化你的任务类构造【所以你构造可以写一些废话】  
        //同时构造你还可传递参数  
        
            return new PendingDispatch(new static(...func_get_args()));
        }
        public static function dispatchNow()
        {
            return app(Dispatcher::class)->dispatchNow(new static(...func_get_args()));
        }
        public static function withChain($chain)
        {
            return new PendingChain(static::class, $chain);
        }
    }
    ```  
    
    Illuminate\Foundation\Bus\PendingDispatch 等待调度构造  
    
    ```php  
    
    namespace Illuminate\Foundation\Bus;

    use Illuminate\Contracts\Bus\Dispatcher;

    class PendingDispatch
    {
    /**
     * The job.
     *
     * @var mixed
     */
    protected $job;

    /**
     * Create a new pending job dispatch.
     *
     * @param  mixed  $job
     * @return void
     */
    public function __construct($job)
    {   
        //你创建的任务类实例对象
        $this->job = $job;
    }
    
    析构函数【实例化本类，结束时会运行这吊毛】
    public function __destruct()
        {
        
            app(Dispatcher::class)->dispatch($this->job);
        }
    ```  
    
    `app(Dispatcher::class)->dispatch($this->job)`干的工作流程   
    
    运行  
    ```php  
    function ($app) {
    // Illuminate\Bus\Dispatcher
                return new Dispatcher($app, function ($connection = null) use ($app) {
                    return $app[QueueFactoryContract::class]->connection($connection);
                });
            }
    ```  
    Illuminate\Bus\Dispatcher构造   
    ```php  
    namespace Illuminate\Bus;
    
    use Closure;
    use RuntimeException;
    use Illuminate\Pipeline\Pipeline;
    use Illuminate\Contracts\Queue\Queue;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Contracts\Container\Container;
    use Illuminate\Contracts\Bus\QueueingDispatcher;
    
    class Dispatcher implements QueueingDispatcher
    {
        /**
         * The container implementation.
         *
         * @var \Illuminate\Contracts\Container\Container
         */
        protected $container;
    
        /**
         * The pipeline instance for the bus.
         *
         * @var \Illuminate\Pipeline\Pipeline
         */
        protected $pipeline;
    
        /**
         * The pipes to send commands through before dispatching.
         *
         * @var array
         */
        protected $pipes = [];
    
        /**
         * The command to handler mapping for non-self-handling events.
         *
         * @var array
         */
        protected $handlers = [];
    
        /**
         * The queue resolver callback.
         *
         * @var \Closure|null
         */
        protected $queueResolver;
    
        /**
         * Create a new command dispatcher instance.
         *
         * @param  \Illuminate\Contracts\Container\Container  $container
         * @param  \Closure|null  $queueResolver
         * @return void
         */
        public function __construct(Container $container, Closure $queueResolver = null)
        {
            $this->container = $container;//Application容器
            /**
            $this->queueResolver=function ($connection = null) use ($app) {
                                return $app[QueueFactoryContract::class]->connection($connection);
            }
            **/
            $this->queueResolver = $queueResolver;
            //Illuminate\Pipeline\Pipeline实例对象
            $this->pipeline = new Pipeline($container);
        }
        
        /**
         * Dispatch a command to its appropriate handler.
         *
         * @param  mixed  $command
         * @return mixed
         */
        public function dispatch($command)
        {
        //$this->commandShouldBeQueued($command)判断是否属于ShouldQueue类
            if ($this->queueResolver && $this->commandShouldBeQueued($command)) {
                return $this->dispatchToQueue($command);
            }
            //不属于就运行此方法【后面解释】
            return $this->dispatchNow($command);
        }
        
        protected function commandShouldBeQueued($command)
            {
                return $command instanceof ShouldQueue;
            }
            
            
            
        public function dispatchToQueue($command)
            {
            //任务所属连接
                $connection = $command->connection ?? null;
                /**
                function ($connection = null) use ($app) {
                //实例队列管理器
                   return $app[QueueFactoryContract::class]->connection($connection);
                }
                **/  
                
                //默认取得SyncQueue队列类实例
                $queue = call_user_func($this->queueResolver, $connection);
        
                if (! $queue instanceof Queue) {
                    throw new RuntimeException('Queue resolver did not return a Queue implementation.');
                }
                //判断任务类是否有queue方法【我们创建的任务没有此方法】
                if (method_exists($command, 'queue')) {
                    return $command->queue($queue, $command);
                }
        
                return $this->pushCommandToQueue($queue, $command);
            }
        
    }
    ```  
    
    `$queue = call_user_func($this->queueResolver, $connection);`   
    实例化队列管理器 Illuminate\Queue\QueueServiceProvider下的方法 
    ```php  
    function ($app) {
           //实例化并传递给匿名函数运行【tap函数功能看下helpers.php就知道了】   
            return tap(new QueueManager($app), function ($manager) {
                $this->registerConnectors($manager);
            });
        }
        
     //注册各种队列连接器
     public function registerConnectors($manager)
         {
             foreach (['Null', 'Sync', 'Database', 'Redis', 'Beanstalkd', 'Sqs'] as $connector) {
                 $this->{"register{$connector}Connector"}($manager);
             }
         }
     
     //sync连队连接器
     protected function registerSyncConnector($manager)
         {
             $manager->addConnector('sync', function () {
                 return new SyncConnector;
             });
         }
    ```  
    
    队列管理器获取连接Illuminate\Queue\QueueManager  
    ```php  
    public function connection($name = null)
        {
        //你不配置默认就是sync
            $name = $name ?: $this->getDefaultDriver();
            //生产好的队列连接实例放在连接池里
            if (! isset($this->connections[$name])) {
                $this->connections[$name] = $this->resolve($name);
    
                $this->connections[$name]->setContainer($this->app);
            }
    
            return $this->connections[$name];
        }
        
    public function getDefaultDriver()
        {
        //获取队列默认连接
            return $this->app['config']['queue.default'];
        }
        
    处理队列连接  
    protected function resolve($name)
        {   
            //得到连接配置选项数组
            $config = $this->getConfig($name);
            //取得队列连接器【不配置情况下默认是sync队列连接器】
            return $this->getConnector($config['driver'])
                            ->connect($config)//执行后返回此类实例  Illuminate\Queue\SyncQueue extend  Illuminate\Queue\Queue 
                            ->setConnectionName($name);
        }
    //取得队列连接器
    protected function getConnector($driver)
        {
        
            if (! isset($this->connectors[$driver])) {
                throw new InvalidArgumentException("No connector for [$driver]");
            }
            //运行队列连接器
            return call_user_func($this->connectors[$driver]);
        }
    ```  
    
    运行默认的sync队列连接器匿名函数  
    ```php  
    function () {
    //Illuminate\Queue\Connectors\SyncConnector
                return new SyncConnector;
            }
    ```
    Sync队列连接器连接  
    ```php  
    namespace Illuminate\Queue\Connectors;
    
    use Illuminate\Queue\SyncQueue;
    
    class SyncConnector implements ConnectorInterface
    {
        /**
         * Establish a queue connection.
         *
         * @param  array  $config
         * @return \Illuminate\Contracts\Queue\Queue
         */
        public function connect(array $config)
        {
        //Illuminate\Queue\SyncQueue
            return new SyncQueue;
        }
    }
    ```    
    Sync队列类
    Illuminate\Queue\SyncQueue extend  Illuminate\Queue\Queue 
    ```php  
    namespace Illuminate\Queue;
    
    use Exception;
    use Throwable;
    use Illuminate\Queue\Jobs\SyncJob;
    use Illuminate\Contracts\Queue\Job;
    use Illuminate\Contracts\Queue\Queue as QueueContract;
    use Symfony\Component\Debug\Exception\FatalThrowableError;
    
    class SyncQueue extends Queue implements QueueContract
    {
        /**
         * Get the size of the queue.
         *
         * @param  string|null  $queue
         * @return int
         */
        public function size($queue = null)
        {
            return 0;
        }
        
    }
    基类方法  
    public function setConnectionName($name)
        {
            $this->connectionName = $name;
    
            return $this;
        }
    ```   
    
    Illuminate\Bus\Dispatcher->pushCommandToQueue()  
    ```php  
    //$queue=默认是SyncQueue实例
    //$command=你创建的任务实例
    protected function pushCommandToQueue($queue, $command)
        {
        //任务类是否指定了队列连接
            if (isset($command->queue, $command->delay)) {
                return $queue->laterOn($command->queue, $command->delay, $command);
            }
    
            if (isset($command->queue)) {
                return $queue->pushOn($command->queue, $command);
            }
    
            if (isset($command->delay)) {
                return $queue->later($command->delay, $command);
            }
            //队列添加任务实例
            return $queue->push($command);
        }
    ```  
    
    默认队列Illuminate\Queue\SyncQueue->push()  任务进队操作  
    ```php  
    public function push($job, $data = '', $queue = null)
        {
            $queueJob = $this->resolveJob($this->createPayload($job, $queue, $data), $queue);
    
            try {
            //事件调度毛线【目前它根本没有用】
                $this->raiseBeforeJobEvent($queueJob);
    
                $queueJob->fire();
    
                $this->raiseAfterJobEvent($queueJob);
            } catch (Exception $e) {
                $this->handleException($queueJob, $e);
            } catch (Throwable $e) {
                $this->handleException($queueJob, new FatalThrowableError($e));
            }
    
            return 0;
        }
    
    protected function resolveJob($payload, $queue)
        {
            return new SyncJob($this->container, $payload, $this->connectionName, $queue);
        }


    protected function createPayload($job, $queue, $data = '')
        {
        //创建任务负荷数组
            $payload = json_encode($this->createPayloadArray($job, $queue, $data));
    
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new InvalidPayloadException(
                    'Unable to JSON encode payload. Error code: '.json_last_error()
                );
            }
    
            return $payload;
        }
        
    protected function createPayloadArray($job, $queue, $data = '')
        {
            return is_object($job)
                        ? $this->createObjectPayload($job, $queue)
                        : $this->createStringPayload($job, $queue, $data);
        }
        
    protected function createObjectPayload($job, $queue)
        {
            $payload = $this->withCreatePayloadHooks($queue, [
                'displayName' => $this->getDisplayName($job),//获取任务类名
                'job' => 'Illuminate\Queue\CallQueuedHandler@call',
                'maxTries' => $job->tries ?? null,//任务的最大尝试次数
                'delay' => $this->getJobRetryDelay($job),//【任务延迟多久后尝试执行】
                'timeout' => $job->timeout ?? null,//任务超时
                'timeoutAt' => $this->getJobExpiration($job),//任务过期时间
                'data' => [//任务内容 
                    'commandName' => $job,
                    'command' => $job,
                ],
            ]);
    
            return array_merge($payload, [
                'data' => [
                    'commandName' => get_class($job),
                    'command' => serialize(clone $job),
                ],
            ]);
        }
        
    protected function createStringPayload($job, $queue, $data)
        {
            return $this->withCreatePayloadHooks($queue, [
                'displayName' => is_string($job) ? explode('@', $job)[0] : null,
                'job' => $job,
                'maxTries' => null,
                'delay' => null,
                'timeout' => null,
                'data' => $data,
            ]);
        }
        
    protected function withCreatePayloadHooks($queue, array $payload)
        {
            if (! empty(static::$createPayloadCallbacks)) {
                foreach (static::$createPayloadCallbacks as $callback) {
                    $payload = array_merge($payload, call_user_func(
                        $callback, $this->getConnectionName(), $queue, $payload
                    ));
                }
            }
    
            return $payload;
        }
        
    protected function getDisplayName($job)【得到任务类的显示名称，没有就返回类名】
        {
            return method_exists($job, 'displayName')
                            ? $job->displayName() : get_class($job);
        }
        
    public function getJobRetryDelay($job)【任务延迟多久后尝试执行】
        {
        //任务类是否存在retryAfter方法
            if (! method_exists($job, 'retryAfter') && ! isset($job->retryAfter)) {
                return;
            }
    
            $delay = $job->retryAfter ?? $job->retryAfter();
    
            return $delay instanceof DateTimeInterface
                            ? $this->secondsUntil($delay) : $delay;
        }
        
    public function getJobExpiration($job)【任务过期时间】
        {
            if (! method_exists($job, 'retryUntil') && ! isset($job->timeoutAt)) {
                return;
            }
    
            $expiration = $job->timeoutAt ?? $job->retryUntil();
    
            return $expiration instanceof DateTimeInterface
                            ? $expiration->getTimestamp() : $expiration;
        }
    ```  
    `  $queueJob->fire();`  任务激活
    ```php  
    namespace Illuminate\Queue\Jobs;
    
    use Illuminate\Queue\Events\JobFailed;
    use Illuminate\Support\InteractsWithTime;
    use Illuminate\Contracts\Events\Dispatcher;
    use Illuminate\Queue\ManuallyFailedException;
    
    abstract class Job
    {
        use InteractsWithTime;
    
        /**
         * The job handler instance.
         *
         * @var mixed
         */
        protected $instance;
    
        /**
         * The IoC container instance.
         *
         * @var \Illuminate\Container\Container
         */
        protected $container;
    
        /**
         * Indicates if the job has been deleted.
         *
         * @var bool
         */
        protected $deleted = false;
    
        /**
         * Indicates if the job has been released.
         *
         * @var bool
         */
        protected $released = false;
    
        /**
         * Indicates if the job has failed.
         *
         * @var bool
         */
        protected $failed = false;
    
        /**
         * The name of the connection the job belongs to.
         *
         * @var string
         */
        protected $connectionName;
    
        /**
         * The name of the queue the job belongs to.
         *
         * @var string
         */
        protected $queue;
    
        /**
         * Get the job identifier.
         *
         * @return string
         */
        abstract public function getJobId();
    
        /**
         * Get the raw body of the job.
         *
         * @return string
         */
        abstract public function getRawBody();
    
        /**
         * Fire the job.
         *
         * @return void
         */
        public function fire()
        {
        /**
        [
          displayName=App\Jobs\Test
          job=Illuminate\Queue\CallQueuedHandler@call
          maxTries=null
          delay=null
          timeout=null
          timeoutAt=null
          data = [
            commandName=App\Jobs\Test
            command=O:13:"App\Jobs\Test":7:{s:6:" * job";N;s:10:"connection";N;s:5:"queue";N;s:15:"chainConnection";N;s:10:"chainQueue";N;s:5:"delay";N;s:7:"chained";a:0:{}}
          ]
        ]
        **/
            $payload = $this->payload();
            //$method=call
            //$class=Illuminate\Queue\CallQueuedHandler
            [$class, $method] = JobName::parse($payload['job']);
            //$this->resolve($class)) 实例化
            //Illuminate\Queue\CallQueuedHandler->call();
            ($this->instance = $this->resolve($class))->{$method}($this, $payload['data']);
        }
    ```  
        