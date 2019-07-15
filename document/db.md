### 数据库使用  
[首页](../readme.md) [下一页：待定](dispatch.md)  [上一页：App\Http\Kernel的路由调度(寻址)流程](dispatch.md) 

- 数据库DB类的骚流程  
    数据库服务提供类的注册  
    ```php  
    <?php
    
    namespace Illuminate\Database;
    
    use Faker\Factory as FakerFactory;
    use Faker\Generator as FakerGenerator;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\ServiceProvider;
    use Illuminate\Contracts\Queue\EntityResolver;
    use Illuminate\Database\Connectors\ConnectionFactory;
    use Illuminate\Database\Eloquent\QueueEntityResolver;
    use Illuminate\Database\Eloquent\Factory as EloquentFactory;
    
    class DatabaseServiceProvider extends ServiceProvider
    {
      
        public function boot()
        {
            Model::setConnectionResolver($this->app['db']);
    
            Model::setEventDispatcher($this->app['events']);
        }

        public function register()
        {
            Model::clearBootedModels();
    
            $this->registerConnectionServices();
    
            $this->registerEloquentFactory();
    
            $this->registerQueueableEntityResolver();
        }

        protected function registerConnectionServices()
        {
            
            $this->app->singleton('db.factory', function ($app) {
                return new ConnectionFactory($app);
            });

            $this->app->singleton('db', function ($app) {
                return new DatabaseManager($app, $app['db.factory']);
            });
    
            $this->app->bind('db.connection', function ($app) {
                return $app['db']->connection();
            });
        }

        protected function registerEloquentFactory()
        {
            $this->app->singleton(FakerGenerator::class, function ($app) {
                return FakerFactory::create($app['config']->get('app.faker_locale', 'en_US'));
            });
    
            $this->app->singleton(EloquentFactory::class, function ($app) {
                return EloquentFactory::construct(
                    $app->make(FakerGenerator::class), $this->app->databasePath('factories')
                );
            });
        }
    
        protected function registerQueueableEntityResolver()
        {
            $this->app->singleton(EntityResolver::class, function () {
                return new QueueEntityResolver;
            });
        }
    }

    ```    
    
    boot方法  
    ```php 
     public function boot()
        {
        //Illuminate\Database\Eloquent\Model
        //$this->app['db']=Illuminate\Database\DatabaseManager实例
        //new DatabaseManager($app, $app['db.factory'])
        //$app['db.factory']=Illuminate\Database\Connectors\ConnectionFactory实例
        
            Model::setConnectionResolver($this->app['db']);
    
            Model::setEventDispatcher($this->app['events']);
        }
    ```  
    
    设置模型的连接处理器  
    //Illuminate\Database\Eloquent\Model->setConnectionResolver()方法  
    ```php  
    public static function setConnectionResolver(Resolver $resolver)
        {
            static::$resolver = $resolver;
        }  
        
        
    ```  
    设置模型事件调度器  
    //Illuminate\Database\Eloquent\Model->setEventDispatcher()方法  
    ```php  
    public static function setEventDispatcher(Dispatcher $dispatcher)
        {
            static::$dispatcher = $dispatcher;
        }
    ```  
    这是我前面列出的代码，它引用了DB【具体实例化过程我不说了，前面已经讲过了】  
    ```php  
    use Illuminate\Support\Facades\DB;
    
    class TestController extends Controller
    {
        //
        function index(Request $request,User $user)
        {
            $data = DB::table("test")->get();
            return view("admin.index",compact('data'));
        }
    }

    ```  
    它最终运行【因为上面注册在Application里了，所以人家去里面找】     
    ```php  
    function ($app) {
        //Illuminate\Database\DatabaseManager 
        //Illuminate\Database\Connectors\ConnectionFactory实例
        
        return new DatabaseManager($app, $app['db.factory']);
     }
    ```  
    
    DB::table()方法  
    ```php  
    //DatabaseManager没有此方法，当然运行魔术方法了
    public function __call($method, $parameters)
        {
            return $this->connection()->$method(...$parameters);
        }
    ```
