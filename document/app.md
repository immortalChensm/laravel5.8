Application运行时类的数据存储情况  
```php  
instances=[
    'path'=>'app',
    'path.base'=>'\',
    'path.lang'=>'resoruces\lang',
    'path.config'=>'config',
    'path.public'=>'public',
    'path.storage'=>'storage',
    'path.database'=>'database',
    'path.resources'=>'resources',
    'path.bootstrap'=>'bootstrap',
    'app'=>'Application实例',
    'Illuminate\Container\Container::class'=>'Application实例',
    'Illuminate\Foundation\PackageManifest::class'=>'Illuminate\Foundation\PackageManifest实例',
    /**
    new PackageManifest(
                new Filesystem, $this->basePath(), $this->getCachedPackagesPath()
            )
    **/ 
    'app'=>'Application实例',
    'request'=>Illuminate\Http\Request实例,
    'config'=>Illuminate\Config\Repository实例,
    'routes'=>Illuminate\Routing\RouteCollection实例,
     Route::class=>Illuminate\Routing\Route实例【http请求匹配到的对象实例】,
    
]

bootedCallbacks=>[
            'function () {
                              $this->app['router']->getRoutes()->refreshNameLookups();
                              $this->app['router']->getRoutes()->refreshActionLookups();
                          }',
            console端运行时注册
            'function () {
                          $this->app->singleton(Schedule::class, function ($app) {
                                     return (new Schedule($this->scheduleTimezone()))
                                             ->useCache($this->scheduleCache());
                         });
                 
                         $schedule = $this->app->make(Schedule::class);
                 
                         $this->schedule($schedule);
                     }
            '
]

reboundCallbacks=[
    'request'=>'function ($app, $request) {
                            $app['url']->setRequest($request);
                        }',
      
    'routes'=>'function ($app, $routes) {
                               $app['url']->setRoutes($routes);
                           }',                  
]
//在FormRequestServiceProviders服务类运行时完成的
afterResolvingCallbacks = [
    Illuminate\Contracts\Validation\ValidatesWhenResolved=>function ($resolved) {
                                                                       $resolved->validateResolved();
                                                                   },
                                                              
]
//在FormRequestServiceProviders服务类运行时完成的
resolvingCallbacks = [
    Illuminate\Foundation\Http\FormRequest=>function ($request, $app) {
                                                        $request = FormRequest::createFrom($app['request'], $request);
                                            
                                                        $request->setContainer($app)->setRedirector($app->make(Redirector::class));
                                                    }     
]


bindings=[
//队列
    'queue'=>'function ($app) {
                         //Illuminate\Queue
                          return tap(new QueueManager($app), function ($manager) {
                              $this->registerConnectors($manager);
                          });
                      }',
                      
    'queue.connection'=>'function ($app) {
                                     return $app['queue']->connection();
                                 }',
                                 
    'queue.worker'=>'function () {
                                //Illuminate\Queue\Worker
                                 return new Worker(
                                     $this->app['queue'], $this->app['events'], $this->app[ExceptionHandler::class]
                                 );
                             }',
                             
    'queue.listener'=>'function () {
                                    //Illuminate\Queue\Worker
                                   return new Listener($this->app->basePath());
                               }',
                               
    'queue.failer'=>'function () {
                                 $config = $this->app['config']['queue.failed'];
                     
                                 return isset($config['table'])
                                             ? $this->databaseFailedJobProvider($config)
                                             : new NullFailedJobProvider;
                             }',
                             
    'Illuminate\Contracts\Bus\Dispatcher'=>'function ($app) {
    //Illuminate\Bus\Dispatcher
                                                        return new Dispatcher($app, function ($connection = null) use ($app) {
                                                            return $app[QueueFactoryContract::class]->connection($connection);
                                                        });
                                                    }',
                           
    'events'=>'function ($app) {
                           return (new Dispatcher($app))->setQueueResolver(function () use ($app) {
                               return $app->make(QueueFactoryContract::class);
                           });
                       }',
                       
    'redis'=>'function ($app) {
                          $config = $app->make('config')->get('database.redis', []);
              
                          return new RedisManager($app, Arr::pull($config, 'client', 'predis'), $config);
                      }',
                      
    'redis.connection'=>'function ($app) {
                                     return $app['redis']->connection();
                                 }',
                       
    'cache'=>'function ($app) {
    //Illuminate\Cache\CacheManager 缓存管理器
                          return new CacheManager($app);
                      }',
                      
    'cache.store'=>'function ($app) {
                                return $app['cache']->driver();
                            }',
                            
    'memcached.connector'=>'function () {
                                        return new MemcachedConnector;
                                    }',
                       
    'log'=>'function () {
                        return new LogManager($this->app);
                    }',  
                    
    'router'=>'function ($app) {
                           return new Router($app['events'], $app);
                       }',
                       
    'url'=>'function ($app) {
                        $routes = $app['router']->getRoutes();
        
                        $app->instance('routes', $routes);
            
                        $url = new UrlGenerator(
                            $routes, $app->rebinding(
                                'request', $this->requestRebinder()
                            ), $app['config']['app.asset_url']
                        );
  
                        $url->setSessionResolver(function () {
                            return $this->app['session'] ?? null;
                        });
            
                        $url->setKeyResolver(function () {
                            return $this->app->make('config')->get('app.key');
                        });
    
                        $app->rebinding('routes', function ($app, $routes) {
                            $app['url']->setRoutes($routes);
                        });
            
                        return $url;
                    }',    
                    
    'redirect'=>'function ($app) {
                             $redirector = new Redirector($app['url']);
    
                             if (isset($app['session.store'])) {
                                 $redirector->setSession($app['session.store']);
                             }
                 
                             return $redirector;
                         }',      
                         
    'ServerRequestInterface::class'=>'function ($app) {
                                                  return (new DiactorosFactory)->createRequest($app->make('request'));
                                              }',  
                                              
    'ResponseInterface::class'=>'function () {
                                             return new PsrResponse;
                                         }',  
                                         
    'Illuminate\Contracts\Routing\ResponseFactory'=>'function ($app) {
                                                   return new ResponseFactory($app[ViewFactoryContract::class], $app['redirect']);
                                               }',   
                                               
    'Illuminate\Routing\Contracts\ControllerDispatcher'=>'function ($app) {
                                                                      return new ControllerDispatcher($app);
                                                                  }',   
                                                                      
    'Illuminate\Contracts\Http\Kernel::class'=>'function ($container, $parameters = []) use (Illuminate\Contracts\Http\Kernel::class, App\Http\Kernel::class) {
                                              if ($abstract == $concrete) {
                                                  return $container->build($concrete);
                                              }
                                  
                                              return $container->resolve(
                                                  $concrete, $parameters, $raiseEvents = false
                                              );
                                          }',    
                                          
    'Illuminate\Contracts\Console\Kernel::class'=>'function ($container, $parameters = []) use (Illuminate\Contracts\Console\Kernel::class, App\Console\Kernel::class) {
                                                  if ($abstract == $concrete) {
                                                      return $container->build($concrete);
                                                  }
                                      
                                                  return $container->resolve(
                                                      $concrete, $parameters, $raiseEvents = false
                                                  );
                                              }',   
    'Illuminate\Contracts\Debug\ExceptionHandler::class'=>'function ($container, $parameters = []) use (Illuminate\Contracts\Debug\ExceptionHandler::class, App\Exceptions\Handler::class) {
                                                      if ($abstract == $concrete) {
                                                          return $container->build($concrete);
                                                      }
                                          
                                                      return $container->resolve(
                                                          $concrete, $parameters, $raiseEvents = false
                                                      );
                                                  }', 
                                                  
    'Illuminate\Contracts\Debug\ExceptionHandler::class'=>'function ($container, $parameters = []) use (Illuminate\Contracts\Debug\ExceptionHandler::class, App\Exceptions\Handler::class) {
                                                          if ($abstract == $concrete) {
                                                              return $container->build($concrete);
                                                          }
                                              
                                                          return $container->resolve(
                                                              $concrete, $parameters, $raiseEvents = false
                                                          );
                                                      }',  
    'db.factory'=>'function ($app) {
    //Illuminate\Database\Connectors\ConnectionFactory
                               return new ConnectionFactory($app);
                           }',       
    
    'db'=>'function ($app) {
    Illuminate\Database\DatabaseManager
                       return new DatabaseManager($app, $app['db.factory']);
                   }',     
                   
    'db.connection'=>' function ($app) {
                                  return $app['db']->connection();
                              }',   
    'Faker\Generator as FakerGenerator'=>'function ($app) {
                                                      return FakerFactory::create($app['config']->get('app.faker_locale', 'en_US'));
                                                  }',    
                                                  
    'Illuminate\Database\Eloquent\Factory as EloquentFactory'=>'function ($app) {
                                                                            return EloquentFactory::construct(
                                                                                $app->make(FakerGenerator::class), $this->app->databasePath('factories')
                                                                            );
                                                                        }', 
    'Illuminate\Contracts\Queue\EntityResolver'=>'function () {
    Illuminate\Database\Eloquent\QueueEntityResolver
                                                              return new QueueEntityResolver;
                                                          }',   
                     
    //view服务提供类注册【运行时自动保存】                                       
    'view'=>'function ($app) {
                         // Next we need to grab the engine resolver instance that will be used by the
                         // environment. The resolver will be used by an environment to get each of
                         // the various engine implementations such as plain PHP or Blade engine.
                         $resolver = $app['view.engine.resolver'];
             
                         $finder = $app['view.finder'];
                         /**
                          protected function createFactory($resolver, $finder, $events)
                             {
                             //Illuminate\View\Factory
                                 return new Factory($resolver, $finder, $events);
                             }
                         **/
                         $factory = $this->createFactory($resolver, $finder, $app['events']);
             
                         // We will also set the container instance on this view environment since the
                         // view composers may be classes registered in the container, which allows
                         // for great testable, flexible composers for the application developer.
                         $factory->setContainer($app);
             
                         $factory->share('app', $app);
             
                         return $factory;
                     }',   
                     
    'view.finder'=>'function ($app) {
    \\Illuminate\View
                                return new FileViewFinder($app['files'], $app['config']['view.paths']);
                            }',    
                            
    'view.engine.resolver'=>'function () {
    \\Illuminate\View
                                         $resolver = new EngineResolver;
                             
                                         foreach (['file', 'php', 'blade'] as $engine) {
                                             $this->{'register'.ucfirst($engine).'Engine'}($resolver);
                                         }
                                         
                                         /**
                                             function registerFileEngine($resolver)
                                             {
                                                 $resolver->register('file', function () {
                                                     return new FileEngine;
                                                 });
                                             }
                                         
                                             function registerPhpEngine($resolver)
                                             {
                                                 $resolver->register('php', function () {
                                                     return new PhpEngine;
                                                 });
                                             }
                                         
                                             function registerBladeEngine($resolver)
                                             {
                                                
                                                 $this->app->singleton('blade.compiler', function () {
                                                     return new BladeCompiler(
                                                         $this->app['files'], $this->app['config']['view.compiled']
                                                     );
                                                 });
                                         
                                                 $resolver->register('blade', function () {
                                                     return new CompilerEngine($this->app['blade.compiler']);
                                                 });
                                             }
                                         **/
                             
                                         return $resolver;
                                     }',                                                                                                                           
                                                                                                                                              
                                                                                                                                           
                                                                                                                                               
                                                                                                                                                                                                                                                                                                                                                
]

aliases=[
          Illuminate\Contracts\Container\Container] => app
          [Illuminate\Contracts\Foundation\Application] => app
          [Psr\Container\ContainerInterface] => app
          [Illuminate\Auth\AuthManager] => auth
          [Illuminate\Contracts\Auth\Factory] => auth
          [Illuminate\Contracts\Auth\Guard] => auth.driver
          [Illuminate\View\Compilers\BladeCompiler] => blade.compiler
          [Illuminate\Cache\CacheManager] => cache
          [Illuminate\Contracts\Cache\Factory] => cache
          [Illuminate\Cache\Repository] => cache.store
          [Illuminate\Contracts\Cache\Repository] => cache.store
          [Illuminate\Config\Repository] => config
          [Illuminate\Contracts\Config\Repository] => config
          [Illuminate\Cookie\CookieJar] => cookie
          [Illuminate\Contracts\Cookie\Factory] => cookie
          [Illuminate\Contracts\Cookie\QueueingFactory] => cookie
          [Illuminate\Encryption\Encrypter] => encrypter
          [Illuminate\Contracts\Encryption\Encrypter] => encrypter
          [Illuminate\Database\DatabaseManager] => db
          [Illuminate\Database\Connection] => db.connection
          [Illuminate\Database\ConnectionInterface] => db.connection
          [Illuminate\Events\Dispatcher] => events
          [Illuminate\Contracts\Events\Dispatcher] => events
          [Illuminate\Filesystem\Filesystem] => files
          [Illuminate\Filesystem\FilesystemManager] => filesystem
          [Illuminate\Contracts\Filesystem\Factory] => filesystem
          [Illuminate\Contracts\Filesystem\Filesystem] => filesystem.disk
          [Illuminate\Contracts\Filesystem\Cloud] => filesystem.cloud
          [Illuminate\Hashing\HashManager] => hash
          [Illuminate\Contracts\Hashing\Hasher] => hash.driver
          [Illuminate\Translation\Translator] => translator
          [Illuminate\Contracts\Translation\Translator] => translator
          [Illuminate\Log\LogManager] => log
          [Psr\Log\LoggerInterface] => log
          [Illuminate\Mail\Mailer] => mailer
          [Illuminate\Contracts\Mail\Mailer] => mailer
          [Illuminate\Contracts\Mail\MailQueue] => mailer
          [Illuminate\Auth\Passwords\PasswordBrokerManager] => auth.password
          [Illuminate\Contracts\Auth\PasswordBrokerFactory] => auth.password
          [Illuminate\Auth\Passwords\PasswordBroker] => auth.password.broker
          [Illuminate\Contracts\Auth\PasswordBroker] => auth.password.broker
          [Illuminate\Queue\QueueManager] => queue
          [Illuminate\Contracts\Queue\Factory] => queue
          [Illuminate\Contracts\Queue\Monitor] => queue
          [Illuminate\Contracts\Queue\Queue] => queue.connection
          [Illuminate\Queue\Failed\FailedJobProviderInterface] => queue.failer
          [Illuminate\Routing\Redirector] => redirect
          [Illuminate\Redis\RedisManager] => redis
          [Illuminate\Contracts\Redis\Factory] => redis
          [Illuminate\Http\Request] => request
          [Symfony\Component\HttpFoundation\Request] => request
          [Illuminate\Routing\Router] => router
          [Illuminate\Contracts\Routing\Registrar] => router
          [Illuminate\Contracts\Routing\BindingRegistrar] => router
          [Illuminate\Session\SessionManager] => session
          [Illuminate\Session\Store] => session.store
          [Illuminate\Contracts\Session\Session] => session.store
          [Illuminate\Routing\UrlGenerator] => url
          [Illuminate\Contracts\Routing\UrlGenerator] => url
          [Illuminate\Validation\Factory] => validator
          [Illuminate\Contracts\Validation\Factory] => validator
          [Illuminate\View\Factory] => view
          [Illuminate\Contracts\View\Factory] => view
          ]  
          
          
          
abstractAliases= [
 [app] => Array
         (
             [0] => 
             [1] => Illuminate\Contracts\Container\Container
             [2] => Illuminate\Contracts\Foundation\Application
             [3] => Psr\Container\ContainerInterface
         )
 
     [auth] => Array
         (
             [0] => Illuminate\Auth\AuthManager
             [1] => Illuminate\Contracts\Auth\Factory
         )
 
     [auth.driver] => Array
         (
             [0] => Illuminate\Contracts\Auth\Guard
         )
 
     [blade.compiler] => Array
         (
             [0] => Illuminate\View\Compilers\BladeCompiler
         )
 
     [cache] => Array
         (
             [0] => Illuminate\Cache\CacheManager
             [1] => Illuminate\Contracts\Cache\Factory
         )
 
     [cache.store] => Array
         (
             [0] => Illuminate\Cache\Repository
             [1] => Illuminate\Contracts\Cache\Repository
         )
 
     [config] => Array
         (
             [0] => Illuminate\Config\Repository
             [1] => Illuminate\Contracts\Config\Repository
         )
 
     [cookie] => Array
         (
             [0] => Illuminate\Cookie\CookieJar
             [1] => Illuminate\Contracts\Cookie\Factory
             [2] => Illuminate\Contracts\Cookie\QueueingFactory
         )
 
     [encrypter] => Array
         (
             [0] => Illuminate\Encryption\Encrypter
             [1] => Illuminate\Contracts\Encryption\Encrypter
         )
 
     [db] => Array
         (
             [0] => Illuminate\Database\DatabaseManager
         )
 
     [db.connection] => Array
         (
             [0] => Illuminate\Database\Connection
             [1] => Illuminate\Database\ConnectionInterface
         )
 
     [events] => Array
         (
             [0] => Illuminate\Events\Dispatcher
             [1] => Illuminate\Contracts\Events\Dispatcher
         )
 
     [files] => Array
         (
             [0] => Illuminate\Filesystem\Filesystem
         )
 
     [filesystem] => Array
         (
             [0] => Illuminate\Filesystem\FilesystemManager
             [1] => Illuminate\Contracts\Filesystem\Factory
         )
 
     [filesystem.disk] => Array
         (
             [0] => Illuminate\Contracts\Filesystem\Filesystem
         )
 
     [filesystem.cloud] => Array
         (
             [0] => Illuminate\Contracts\Filesystem\Cloud
         )
 
     [hash] => Array
         (
             [0] => Illuminate\Hashing\HashManager
         )
 
     [hash.driver] => Array
         (
             [0] => Illuminate\Contracts\Hashing\Hasher
         )
 
     [translator] => Array
         (
             [0] => Illuminate\Translation\Translator
             [1] => Illuminate\Contracts\Translation\Translator
         )
 
     [log] => Array
         (
             [0] => Illuminate\Log\LogManager
             [1] => Psr\Log\LoggerInterface
         )
 
     [mailer] => Array
         (
             [0] => Illuminate\Mail\Mailer
             [1] => Illuminate\Contracts\Mail\Mailer
             [2] => Illuminate\Contracts\Mail\MailQueue
         )
 
     [auth.password] => Array
         (
             [0] => Illuminate\Auth\Passwords\PasswordBrokerManager
             [1] => Illuminate\Contracts\Auth\PasswordBrokerFactory
         )
 
     [auth.password.broker] => Array
         (
             [0] => Illuminate\Auth\Passwords\PasswordBroker
             [1] => Illuminate\Contracts\Auth\PasswordBroker
         )
 
     [queue] => Array
         (
             [0] => Illuminate\Queue\QueueManager
             [1] => Illuminate\Contracts\Queue\Factory
             [2] => Illuminate\Contracts\Queue\Monitor
         )
 
     [queue.connection] => Array
         (
             [0] => Illuminate\Contracts\Queue\Queue
         )
 
     [queue.failer] => Array
         (
             [0] => Illuminate\Queue\Failed\FailedJobProviderInterface
         )
 
     [redirect] => Array
         (
             [0] => Illuminate\Routing\Redirector
         )
 
     [redis] => Array
         (
             [0] => Illuminate\Redis\RedisManager
             [1] => Illuminate\Contracts\Redis\Factory
         )
 
     [request] => Array
         (
             [0] => Illuminate\Http\Request
             [1] => Symfony\Component\HttpFoundation\Request
         )
 
     [router] => Array
         (
             [0] => Illuminate\Routing\Router
             [1] => Illuminate\Contracts\Routing\Registrar
             [2] => Illuminate\Contracts\Routing\BindingRegistrar
         )
 
     [session] => Array
         (
             [0] => Illuminate\Session\SessionManager
         )
 
     [session.store] => Array
         (
             [0] => Illuminate\Session\Store
             [1] => Illuminate\Contracts\Session\Session
         )
 
     [url] => Array
         (
             [0] => Illuminate\Routing\UrlGenerator
             [1] => Illuminate\Contracts\Routing\UrlGenerator
         )
 
     [validator] => Array
         (
             [0] => Illuminate\Validation\Factory
             [1] => Illuminate\Contracts\Validation\Factory
         )
 
     [view] => Array
         (
             [0] => Illuminate\View\Factory
             [1] => Illuminate\Contracts\View\Factory
         )
 ]                          
```  

Illuminate\Routing\Router的数据存储   
```php  
middlewareGroups=[
'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
        ],
]

middleware=[
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
]

protected $events;


protected $container;


protected $routes;


protected $current;


protected $currentRequest;



public $middlewarePriority = [
\Illuminate\Session\Middleware\StartSession::class,
\Illuminate\View\Middleware\ShareErrorsFromSession::class,
\Illuminate\Auth\Middleware\Authenticate::class,
\Illuminate\Session\Middleware\AuthenticateSession::class,
\Illuminate\Routing\Middleware\SubstituteBindings::class,
\Illuminate\Auth\Middleware\Authorize::class,
];


protected $binders = [];


protected $patterns = [];

protected $groupStack = [
    0=>[
        'middleware'=>[
                0=>'web'
        ],
        namespace='App\Http\Controllers'
    ]
];

public static $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
```  

![app1](images/app1.png)
![app1](images/app2.png)
![app1](images/app3.png)
![app1](images/app4.png)
![app1](images/app5.png)
![app1](images/app6.png)
![app1](images/app7.png)
![app1](images/app8.png)
![app1](images/app9.png)
![app1](images/app10.png)
![app1](images/app11.png)
![app1](images/app12.png)
![app1](images/app13.png)
![app1](images/app14.png)
![app1](images/app15.png)
![app1](images/app16.png)
![app1](images/app17.png)
![app1](images/app18.png)
![app1](images/app19.png)    

abstractAliases  
![app1](images/aliases1.png)  
![app1](images/aliases2.png)  
![app1](images/aliases3.png)  
![app1](images/aliases4.png)  
![app1](images/aliases5.png)  
![app1](images/aliases6.png)  
![app1](images/aliases7.png)    

实例池里的部分数据
instances[]  
config  
![app1](images/instances/config1.png) 
![app1](images/instances/config2.png) 
![app1](images/instances/config3.png) 
![app1](images/instances/config4.png) 
![app1](images/instances/config5.png) 
![app1](images/instances/config6.png) 
![app1](images/instances/config7.png) 
![app1](images/instances/config8.png) 
![app1](images/instances/config9.png) 
![app1](images/instances/config10.png) 
![app1](images/instances/config11.png) 
![app1](images/instances/config12.png) 
![app1](images/instances/config13.png) 

events  
![app1](images/instances/events.png)   

PackageManifest  
![app1](images/instances/PackageManifest.png)    

request   
![app1](images/instances/request1.png) 
![app1](images/instances/request2.png)   

routers  
![app1](images/instances/router1.png) 
![app1](images/instances/router2.png)   

routes  
![app1](images/instances/routes_a_1.png) 
![app1](images/instances/routes_a_2.png) 
![app1](images/instances/routes_a_3.png) 
![app1](images/instances/routes_a_4.png) 
![app1](images/instances/routes_a_5.png)   

globalAfterResolvingCallbacks|afterResolvingCallbacks|globalResolvingCallbacks|resolvingCallbacks存储情况   
![app1](images/instances/resolving.png) 

