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
    【Illuminate\Database\DatabaseManager大哥装逼场所】   
    DB::table()方法  
    ```php  
    //DatabaseManager没有此方法，当然运行魔术方法了
    public function __call($method, $parameters)
        {
            return $this->connection()->$method(...$parameters);
        }
    ```  
    
    DatabaseManager->connection()方法   
    ```php  
    public function connection($name = null)
        {
        //得到数据库的连接名称一般是mysql,null
            [$database, $type] = $this->parseConnectionName($name);
    
            $name = $name ?: $database;//mysql
            //正常情况下，这个连接实例是不存在的【开始的时候】 
            //当你第二次再调用它时【如第一次是mysql】，第二次直接返回数据即return
            //不再返回了，它存储的数据是这样的
            /**
            connections[mysql]=obj
            connections[sqlite]=obj
            connections[pgsql]=obj
            ...
            **/
            if (! isset($this->connections[$name])) {
            //存储连接实例
                $this->connections[$name] = $this->configure(
                    $this->makeConnection($database), $type
                );
            }
    
            return $this->connections[$name];
        }
    ```  
    DatabaseManager->parseConnectionName()方法【得到连接名称】   
    ```php  
     protected function parseConnectionName($name)
        {
        //得到数据库的连接名称
            $name = $name ?: $this->getDefaultConnection();
            //判断数组中每个数据元素是否含有指定的$name字符串 
            //除非你要配置主从复制，读写分离的数据库选项，不然返回数组
            return Str::endsWith($name, ['::read', '::write'])
                                ? explode('::', $name, 2) : [$name, null];
        }
    ```    
    
    DatabaseManager->getDefaultConnection()方法【得到默认的连接名称一般是mysql】    
    ```php  
    public function getDefaultConnection()
        {
        //返回mysql【具体自己去打开config/database.php配置文件看】
        //config到底对应什么东西【自己去看一下Application运行时动态的数据存储情况】
            return $this->app['config']['database.default'];
        }
    ```  
    
    DatabaseManager类的方法打印  
    ```php  
        namespace App\Http\Controllers\Admin;
        
        use App\User;
        use Illuminate\Database\DatabaseManager;
        use Illuminate\Http\Request;
        use App\Http\Controllers\Controller;
        use Illuminate\Support\Facades\DB;
        use Symfony\Component\Routing\RouteCollection;
        
        class TestController extends Controller
        {
            //
            function index(Request $request,User $user)
            {
                $data = DB::table("test")->get();
                /**@var RouteCollection */
               //print_r(app('routes')->get(app('request')->getMethod()));
                /** @var Request */
                //print_r(app("request")->headers->get('user-agent'));
                /** @var DatabaseManager $db */
                $db = app("db");
                $obj = new \ReflectionClass($db);
                print_r($obj->getMethods());//打印出它的所有方法
                //这样子的话，你就可以使用它了，后面我们会具体使用它的方法
        
                return view("admin.index",compact('data'));
            }
        }
    ```  
    
    [Str::endsWith方法说明](Str.md)     
    
    DatabaseManager->makeConnection($database)创建连接方法  
    ```php  
     protected function makeConnection($name)
        {
        //得到配置数组【默认是mysql】
            $config = $this->configuration($name);
            //这里是没有东西的【不用管了，后面再说】
            if (isset($this->extensions[$name])) {
                return call_user_func($this->extensions[$name], $config, $name);
            }
    
            if (isset($this->extensions[$driver = $config['driver']])) {
                return call_user_func($this->extensions[$driver], $config, $name);
            }
            //没有错就调用这句 
            //大家应该清楚Illuminate是一级目录，后面的为二级目录【即它自己的内置包】 
            //Connectors三级目录 ，后面才是类连接工厂
            //factory=Illuminate\Database\Connectors\ConnectionFactory实例  
            //我怎么知道的，看上面，上面的数据库服务提供类已经注册了
            return $this->factory->make($config, $name);
        }
    ```  
     DatabaseManager->configuration($name)得到数据库连接配置选项【得到mysql配置数组】
    ```php  
    protected function configuration($name)
        {
        //得到连接选项名称【默认为mysql】
            $name = $name ?: $this->getDefaultConnection();
            /**
            'connections' => [
            
                    'sqlite' => [
                        'driver' => 'sqlite',
                        'url' => env('DATABASE_URL'),
                        'database' => env('DB_DATABASE', database_path('database.sqlite')),
                        'prefix' => '',
                        'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
                    ],
            
                    'mysql' => [
                        'driver' => 'mysql',
                        'url' => env('DATABASE_URL'),
                        'host' => env('DB_HOST', '127.0.0.1'),
                        'port' => env('DB_PORT', '3306'),
                        'database' => env('DB_DATABASE', 'forge'),
                        'username' => env('DB_USERNAME', 'forge'),
                        'password' => env('DB_PASSWORD', ''),
                        'unix_socket' => env('DB_SOCKET', ''),
                        'charset' => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                        'prefix' => '',
                        'prefix_indexes' => true,
                        'strict' => true,
                        'engine' => null,
                        'options' => extension_loaded('pdo_mysql') ? array_filter([
                            PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                        ]) : [],
                    ],
            
                    'pgsql' => [
                        'driver' => 'pgsql',
                        'url' => env('DATABASE_URL'),
                        'host' => env('DB_HOST', '127.0.0.1'),
                        'port' => env('DB_PORT', '5432'),
                        'database' => env('DB_DATABASE', 'forge'),
                        'username' => env('DB_USERNAME', 'forge'),
                        'password' => env('DB_PASSWORD', ''),
                        'charset' => 'utf8',
                        'prefix' => '',
                        'prefix_indexes' => true,
                        'schema' => 'public',
                        'sslmode' => 'prefer',
                    ],
            
                    'sqlsrv' => [
                        'driver' => 'sqlsrv',
                        'url' => env('DATABASE_URL'),
                        'host' => env('DB_HOST', 'localhost'),
                        'port' => env('DB_PORT', '1433'),
                        'database' => env('DB_DATABASE', 'forge'),
                        'username' => env('DB_USERNAME', 'forge'),
                        'password' => env('DB_PASSWORD', ''),
                        'charset' => 'utf8',
                        'prefix' => '',
                        'prefix_indexes' => true,
                    ],
            
                ]
            **/
            $connections = $this->app['config']['database.connections'];
            //Arr:get不用了吧，得到mysql索引的数组返回
            //没有就扔个参数错误异常给你
            if (is_null($config = Arr::get($connections, $name))) {
                throw new InvalidArgumentException("Database [{$name}] not configured.");
            }
    
            return (new ConfigurationUrlParser)
                        ->parseConfiguration($config);
        }
    ```  
    Illuminate\Support\ConfigurationUrlParser类【配置解析，本处原样返回】 
    ```php  
    public function parseConfiguration($config)
        {
        //配置选项是数组的，所以这里判断没有用的
            if (is_string($config)) {
                $config = ['url' => $config];
            }
    
            $url = $config['url'] ?? null;
            //删除uri选项 
            //添加完了又删除
            $config = Arr::except($config, 'url');
            //uri不存在直接返回，存在下面就解析了
            if (! $url) {
                return $config;
            }
    
            $parsedUrl = $this->parseUrl($url);
    
            return array_merge(
                $config,
                $this->getPrimaryOptions($parsedUrl),
                $this->getQueryOptions($parsedUrl)
            );
        }
    ```  
    
    Illuminate\Database\Connectors\ConnectionFactory连接工厂构造
    ```php  
    namespace Illuminate\Database\Connectors;
    
    use PDOException;
    use Illuminate\Support\Arr;
    use InvalidArgumentException;
    use Illuminate\Database\Connection;
    use Illuminate\Database\MySqlConnection;
    use Illuminate\Database\SQLiteConnection;
    use Illuminate\Database\PostgresConnection;
    use Illuminate\Database\SqlServerConnection;
    use Illuminate\Contracts\Container\Container;
    
    class ConnectionFactory
    {
        /**
         * The IoC container instance.
         *
         * @var \Illuminate\Contracts\Container\Container
         */
        protected $container;
    
        /**
         * Create a new connection factory instance.
         *
         * @param  \Illuminate\Contracts\Container\Container  $container
         * @return void
         */
        public function __construct(Container $container)
        {
            $this->container = $container;
        }
    ```  
    Illuminate\Database\Connectors\ConnectionFactory->make()连接工厂开始制造
    ```php  
    /**
    array $config, $name = null生产原料
    **/
     public function make(array $config, $name = null)
        {
        //给配置数组选项添加prefix,name选项返回
            $config = $this->parseConfig($config, $name);
            //这个是不会存在的【如果你的项目搞多读写库，就自己看】  
            //一般我们用一些第三方的中间件就可以了
            if (isset($config['read'])) {
                return $this->createReadWriteConnection($config);
            }
    
            return $this->createSingleConnection($config);
        }
    ```  
    连接工厂制造单一连接
    Illuminate\Database\Connectors\ConnectionFactory->createSingleConnection($config)  
    ```php  
    protected function createSingleConnection(array $config)
        {
        
            /**
            根据数据库的配置选项【决定要使用哪个连接器】
            //返回的内容就是这样【我简化了】
            function ()use($config){
                return new Illuminate\Database\Connectors\MySqlConnector();
            }
            **/
            $pdo = $this->createPdoResolver($config);
    
            return $this->createConnection(
                $config['driver'], $pdo, $config['database'], $config['prefix'], $config
            );
        }
    ```  
    
    Illuminate\Database\Connectors\ConnectionFactory->createPdoResolver($config)  
    ```php  
     protected function createPdoResolver(array $config)
        {
        
            return array_key_exists('host', $config)
                                ? $this->createPdoResolverWithHosts($config)
                                : $this->createPdoResolverWithoutHosts($config);
        }
    ```    
    
     Illuminate\Database\Connectors\ConnectionFactory->createPdoResolverWithHosts($config)  
     
     ```php  
      protected function createPdoResolverWithHosts(array $config)
         {
         //返回一个匿名函数
             return function () use ($config) {
             /**
             $hosts = Arr::wrap($config['host']);
            
             $hosts = [$hosts];
             **/
             //shuffle 随机打乱数组【老外真闲，套路一套套的，获取一个配置参数也搞这么多】
                 foreach (Arr::shuffle($hosts = $this->parseHosts($config)) as $key => $host) {
                     $config['host'] = $host;
                     //得到数据库的ip
                     
                     try {
                         return $this->createConnector($config)->connect($config);
                     } catch (PDOException $e) {
                         continue;
                     }
                 }
     
                 throw $e;
             };
         }
     ```    
     
     Illuminate\Database\Connectors\ConnectionFactory->createConnector($config)   
     
     ```php  
     public function createConnector(array $config)
         {
             if (! isset($config['driver'])) {
                 throw new InvalidArgumentException('A driver must be specified.');
             }
             //是否绑定了，没有绑定不用理它
             if ($this->container->bound($key = "db.connector.{$config['driver']}")) {
                 return $this->container->make($key);
             }
     
             switch ($config['driver']) {
                 case 'mysql'://我们要用的就是Mysql【其它的连接方式自己看了】
                     return new MySqlConnector;
                 case 'pgsql':
                     return new PostgresConnector;
                 case 'sqlite':
                     return new SQLiteConnector;
                 case 'sqlsrv':
                     return new SqlServerConnector;
             }
     
             throw new InvalidArgumentException("Unsupported driver [{$config['driver']}]");
         }
     ```  
     
     Illuminate\Database\Connectors\MySqlConnector mysql连接器  
     
     //连接工厂创建连接
     Illuminate\Database\Connectors\ConnectionFactory->createConnection($config)   
     
     ```php
     //$config['driver'], $pdo, $config['database'], $config['prefix'], $config  
     protected function createConnection($driver, $connection, $database, $prefix = '', array $config = [])
         {
         
         //这个也不用管它
             if ($resolver = Connection::getResolver($driver)) {
                 return $resolver($connection, $database, $prefix, $config);
             }
     
             switch ($driver) {
                 case 'mysql'://看这个就行了
                     return new MySqlConnection($connection, $database, $prefix, $config);
                 case 'pgsql':
                     return new PostgresConnection($connection, $database, $prefix, $config);
                 case 'sqlite':
                     return new SQLiteConnection($connection, $database, $prefix, $config);
                 case 'sqlsrv':
                     return new SqlServerConnection($connection, $database, $prefix, $config);
             }
     
             throw new InvalidArgumentException("Unsupported driver [{$driver}]");
         }
     ```    
     
     数据库连接MySqlConnector->connect()  
     ```php  
      public function connect(array $config)
         {
         //得到dsn连接配置参数
         //支持本地域unix_socket协议  
         
         //数据库的连接支持PF_INET,PF_INET6,PF_UNIX协议
             $dsn = $this->getDsn($config);
     //得到配置选项
             $options = $this->getOptions($config);
             //得到pdo连接实例
             $connection = $this->createConnection($dsn, $config, $options);
             //选择数据库
             if (! empty($config['database'])) {
                 $connection->exec("use `{$config['database']}`;");
             }
             //设置编码
             $this->configureEncoding($connection, $config);
             //设置时区
             $this->configureTimezone($connection, $config);
             //设置数据库模式【模式会影响数据库什么，请自行查资料】
             $this->setModes($connection, $config);
             //返回pdo
             return $connection;
         }
     ```  
     
     数据库连接MySqlConnector->createConnection() 
     ```php  
     public function createConnection($dsn, array $config, array $options)
         {
             [$username, $password] = [
                 $config['username'] ?? null, $config['password'] ?? null,
             ];
     
             try {
                 return $this->createPdoConnection(
                     $dsn, $username, $password, $options
                 );
             } catch (Exception $e) {
                 return $this->tryAgainIfCausedByLostConnection(
                     $e, $dsn, $username, $password, $options
                 );
             }
         }
     ```  
     
     数据库连接MySqlConnector->createPdoConnection()  
     ```php  
     protected function createPdoConnection($dsn, $username, $password, $options)
         {
             if (class_exists(PDOConnection::class) && ! $this->isPersistentConnection($options)) {
                 return new PDOConnection($dsn, $username, $password, $options);
             }
             //默认返回pdo实例 
             //pdo操作数据库是基本功了【不解释了】
             return new PDO($dsn, $username, $password, $options);
         }
     ```   
     
     Illuminate\Database\MySqlConnection extends Illuminate\Database\Connection构造器  
     ```php  
     public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
         {
             $this->pdo = $pdo;
    
             $this->database = $database;
     
             $this->tablePrefix = $tablePrefix;
     
             $this->config = $config;
     
             $this->useDefaultQueryGrammar();
     
             $this->useDefaultPostProcessor();
         }
     ```
     Illuminate\Database\MySqlConnection extends Illuminate\Database\Connection->useDefaultQueryGrammar()方法  
     ```php  
     public function useDefaultQueryGrammar()
         {
         //new Illuminate\Database\Query\Grammars\Grammar
             $this->queryGrammar = $this->getDefaultQueryGrammar();
         }
     ``` 
     Illuminate\Database\MySqlConnection extends Illuminate\Database\Connection->useDefaultPostProcessor()方法  
     ```php  
      public function useDefaultPostProcessor()
         {
         //Illuminate\Database\Query\Processors\Processor
             $this->postProcessor = $this->getDefaultPostProcessor();
         }
     ```
     
     DatabaseManager的流程概括
     ```php  
          第一步：DatabaseManager->connection() 
          第二步：DatabaseManager->makeConnection(){$connections【mysql】=Illuminate\Database\Connectors\ConnectionFactory->make()}  
          第三步：ConnectionFactory->make(){
                 $pdo = function () use ($config) {
                                return $this->createConnector($config){
                                      switch ($config['driver']) {
                                                 case 'mysql':
                                                     return new MySqlConnector;
                                                 case 'pgsql':
                                                     return new PostgresConnector;
                                                 case 'sqlite':
                                                     return new SQLiteConnector;
                                                 case 'sqlsrv':
                                                     return new SqlServerConnector;
                                             }
                                }->connect($config)//connection执行后会返回PDO数据库的连接实例;
                        };
                 
                 return $this->createConnection(
                     $config['driver'], $pdo, $config['database'], $config['prefix'], $config
                 ){
                         switch ($driver) {
                             case 'mysql':
                             //返回Illuminate\Database\MySqlConnection extends Illuminate\Database\Connection实例对象
                                 return new MySqlConnection($connection, $database, $prefix, $config);
                             case 'pgsql':
                                 return new PostgresConnection($connection, $database, $prefix, $config);
                             case 'sqlite':
                                 return new SQLiteConnection($connection, $database, $prefix, $config);
                             case 'sqlsrv':
                                 return new SqlServerConnection($connection, $database, $prefix, $config);
                         }
                 };
          }  
     ```  
     四大连接器【mysql,sqlite,sqlserver,postgres】都是继承Connector  
     ![连接器](images/connector.png)  
     
     四大连接【mysql,sqlite,sqlserver,postgres】都是继承Connection  
     ![连接器](images/connection.png)  
     
     所以当DB::table()的时候，它会根据数据库的连接类型【mysql,sqlite,sqlserver,pgsql】让连接工厂生产不同的   
     的数据库连接返回，同时连接即Connection是基于连接器Connector的  
     连接器Connector也是根据参数决定要生产什么连接器返回【PDO】
     
     连接工厂使用了工厂模式，根据参数不同返回不同的制造产品  
     
     连接工厂生产出来的连接Connection保存在数据库管理器的DatabaseManager->connections数组里，下次使用直接取出来  
     
     那么DB的实例化过程就此说完了.  
     
     下面我们要说它的方法table()了
     这个方法当然是Illuminate\Database\MySqlConnection extends  Illuminate\Database\Connection->table()了  
     ```php  
     public function table($table)
         {
             return $this->query()->from($table);
         }
     ```  
     Illuminate\Database\MySqlConnection extends  Illuminate\Database\Connection->query()方法
     ```php  
     public function query()
         {
         //Illuminate\Database\Query\Builder实例【就叫查询构建器吧】
             return new QueryBuilder(
             //$this->getQueryGrammar()返回Illuminate\Database\Query\Grammars\Grammar实例对象
             //$this->getPostProcessor()返回Illuminate\Database\Query\Processors\Processor实例对象
                 $this, $this->getQueryGrammar(), $this->getPostProcessor()
             );
         }
     ```
     
     
     
   
      
   