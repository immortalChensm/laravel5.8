### session  
- session服务提供类注册  
```php  
<?php

namespace Illuminate\Session;

use Illuminate\Support\ServiceProvider;
use Illuminate\Session\Middleware\StartSession;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerSessionManager();

        $this->registerSessionDriver();
        //注册启动session中间件 
        //特别留意此中间件【框架在运行之前，是先运行中间件的，全局先运行，再运行路由分组中间件默认是web分组】
        $this->app->singleton(StartSession::class);
    }

    /**
     * Register the session manager instance.
     *
     * @return void
     */
    protected function registerSessionManager()
    {
    //注册session管理器 【前面说过不少如缓存管理器，队列管理器，数据库管理器】
        $this->app->singleton('session', function ($app) {
            return new SessionManager($app);
        });
    }

    /**
     * Register the session driver instance.
     *
     * @return void
     */
    protected function registerSessionDriver()
    {
    //注册session存储
        $this->app->singleton('session.store', function ($app) {
            // First, we will create the session manager which is responsible for the
            // creation of the various session drivers when they are needed by the
            // application instance, and will resolve them on a lazy load basis.
            return $app->make('session')->driver();
        });
    }
}

```  

StartSession中间件类运行handle    
```php  
<?php

namespace Illuminate\Session\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Session\SessionManager;
use Illuminate\Contracts\Session\Session;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class StartSession
{
    /**
     * The session manager.
     *
     * @var \Illuminate\Session\SessionManager
     */
    protected $manager;

    /**
     * Create a new session middleware.
     *
     * @param  \Illuminate\Session\SessionManager  $manager
     * @return void
     */
    public function __construct(SessionManager $manager)
    {
    //得到session管理器
        $this->manager = $manager;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //没有配置session启动的话直接继续
        if (! $this->sessionConfigured()) {
            return $next($request);
        }

        $request->setLaravelSession(
        //启动session
            $session = $this->startSession($request)
        );

        $this->collectGarbage($session);

        $response = $next($request);
        /**
        Illuminate\Session\Store->setPreviousUrl($url)
            {
                $this->put('_previous.url', $url);
            }
        **/
        $this->storeCurrentUrl($request, $session);

        //给响应头设置cookies
        $this->addCookieToResponse($response, $session);
        
        //响应结束后，将用户使用session过程中设置的数据保存在文件中
        $this->saveSession($request);

        return $response;
    }

    /**
     * Start the session for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Session\Session
     */
    protected function startSession(Request $request)
    {
    //$this->getSession($request) Session\Store实例 并设置了sessionId
        return tap($this->getSession($request), function ($session) use ($request) {
            $session->setRequestOnHandler($request);
            //启动session 
            //默认是从文件读取内容
            $session->start();
        });
    }
    
    Illuminate\Session\Store->start()
        {
            $this->loadSession();
    
            if (! $this->has('_token')) {
            //没有_token属性时则生成
                $this->regenerateToken();
            }
    
            return $this->started = true;
        }
        
    Illuminate\Session\Store->loadSession()
        {
        //读取session文件的内容然后保存在此成员
            $this->attributes = array_merge($this->attributes, $this->readFromHandler());
        }
        
    Illuminate\Session\Store->readFromHandler()
        {
        //Illuminate\Session\FileSessionHandler 默认文件file
        //$this->getId() 这个 是取得cookie设置的APP_NAME_session对应的键值
            if ($data = $this->handler->read($this->getId())) {
            //序列化 处理
                $data = @unserialize($this->prepareForUnserialize($data));
    
                if ($data !== false && ! is_null($data) && is_array($data)) {
                    return $data;
                }
            }
    
            return [];
        }
        
    //Illuminate\Session\FileSessionHandler->read($sessionId)
        {
            if ($this->files->isFile($path = $this->path.'/'.$sessionId)) {
                if ($this->files->lastModified($path) >= Carbon::now()->subMinutes($this->minutes)->getTimestamp()) {
                    return $this->files->sharedGet($path);
                }
            }
    
            return '';
        }

    返回Session\Store实例 并根据当前的请求cookies设置它的唯一id
    public function getSession(Request $request)
    {
    //$this->manager->driver() 获取session驱动 默认获取文件驱动并封装为Session\Store实例返回
        return tap($this->manager->driver(), function ($session) use ($request) {
        //然后设置session的id  
        //$session->getName() 得到 laravel_session 
        //$request->cookies 当前提交的cookie 含有laravel_session内容时【默认看你配置的APP_NAME是什么】  
        //则在http请求时会设置xxx_lession=xxx 
            $session->setId($request->cookies->get($session->getName()));
        });
    }
    
    //获取session驱动 
    Illuminate\Session\SessionManager->driver($driver = null)
        {
        //获取默认的驱动名称默认是file文件
            $driver = $driver ?: $this->getDefaultDriver();
    
            if (is_null($driver)) {
                throw new InvalidArgumentException(sprintf(
                    'Unable to resolve NULL driver for [%s].', static::class
                ));
            }
   
            if (! isset($this->drivers[$driver])) {
                $this->drivers[$driver] = $this->createDriver($driver);
            }
            //驱动存在直接返回
            return $this->drivers[$driver];
        }
    //创建驱动
    Illuminate\Session\SessionManager->createDriver($driver)
        {
           //是否存在用户自定义的驱动【默认木有】
            if (isset($this->customCreators[$driver])) {
                return $this->callCustomCreator($driver);
            } else {
            //运行驱动方法
                $method = 'create'.Str::studly($driver).'Driver';
    
                if (method_exists($this, $method)) {
                    return $this->$method();
                }
            }
            throw new InvalidArgumentException("Driver [$driver] not supported.");
        }
    Illuminate\Session\SessionManager->createFileDriver()
        {
            return $this->createNativeDriver();
        }
        
    Illuminate\Session\SessionManager->createNativeDriver()
        {   
        //得到session的生命周期
            $lifetime = $this->app['config']['session.lifetime'];
    
            return $this->buildSession(new FileSessionHandler(
                $this->app['files'], $this->app['config']['session.files'], $lifetime
            ));
        }
        
    Illuminate\Session\SessionManager->buildSession($handler)
        {
            return $this->app['config']['session.encrypt']//session是否启用加密了 默认不加密
                    ? $this->buildEncryptedSession($handler)
                    : new Store($this->app['config']['session.cookie'], $handler);
        }
    
    //session存储
    Illuminate\Session\Store->__construct($name, SessionHandlerInterface $handler, $id = null)
                                  {
                                      $this->setId($id);//generateSessionId 生成随机字符串
                                      $this->name = $name;//laravel_session
                                      $this->handler = $handler;//FileSessionHandler实例对象
                                  }
                                  
    Illuminate\Session\Store->generateSessionId()
                                      {
                                          return Str::random(40);
                                      }
        
    文件session处理器
    Illuminate\Session\FileSessionHandler
    Illuminate\Session\FileSessionHandler __construct(Filesystem $files, $path, $minutes)
        {
            $this->path = $path;//session存储目录   【所以session使用时，记得用数据库，免得分布式部署时出问题】   
            $this->files = $files;//文件系统
            $this->minutes = $minutes;//过期时间  分钟
        }
    /**
     * Remove the garbage from the session if necessary.
     *
     * @param  \Illuminate\Contracts\Session\Session  $session
     * @return void
     */
    protected function collectGarbage(Session $session)
    {
        $config = $this->manager->getSessionConfig();

        //session文件自动回收
        if ($this->configHitsLottery($config)) {
            $session->getHandler()->gc($this->getSessionLifetimeInSeconds());
        }
    }

    /**
     * Determine if the configuration odds hit the lottery.
     *
     * @param  array  $config
     * @return bool
     */
    protected function configHitsLottery(array $config)
    {
        return random_int(1, $config['lottery'][1]) <= $config['lottery'][0];
    }

    /**
     * Store the current URL for the request if necessary.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Contracts\Session\Session  $session
     * @return void
     */
    protected function storeCurrentUrl(Request $request, $session)
    {
        if ($request->method() === 'GET' &&
            $request->route() &&
            ! $request->ajax() &&
            ! $request->prefetch()) {
            $session->setPreviousUrl($request->fullUrl());
        }
    }

    //创建cookie响应头
    protected function addCookieToResponse(Response $response, Session $session)
    {
    //判断session驱动是否是永久存储  因为它还有数组方式存储
        if ($this->sessionIsPersistent($config = $this->manager->getSessionConfig())) {
        //设置响应头
            $response->headers->setCookie(new Cookie(
            //$session->getName() session名称默认是应用名称_session形式
            //$session->getId() 40个长度的随机字符
            //$this->getCookieExpirationDate() 得到过期时间
                $session->getName(), $session->getId(), $this->getCookieExpirationDate(),
                $config['path'], $config['domain'], $config['secure'] ?? false,
                $config['http_only'] ?? true, false, $config['same_site'] ?? null
            ));
        }
    }

    /**
     * Save the session data to storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function saveSession($request)
    {
        $this->manager->driver()->save();
    }

    /**
     * Get the session lifetime in seconds.
     *
     * @return int
     */
    protected function getSessionLifetimeInSeconds()
    {
        return ($this->manager->getSessionConfig()['lifetime'] ?? null) * 60;
    }

    /**
     * Get the cookie lifetime in seconds.
     *
     * @return \DateTimeInterface|int
     */
    protected function getCookieExpirationDate()
    {
        $config = $this->manager->getSessionConfig();

        return $config['expire_on_close'] ? 0 : Date::instance(
            Carbon::now()->addRealMinutes($config['lifetime'])
        );
    }

    //获取session驱动是否配置了
    protected function sessionConfigured()
    {
        //获取Illuminate\Session\SessionManager
        return ! is_null($this->manager->getSessionConfig()['driver'] ?? null);
    }
    
    获取Illuminate\Session\SessionManager->getSessionConfig()
     {
         return $this->app['config']['session'];
     }
  
    protected function sessionIsPersistent(array $config = null)
    {
        $config = $config ?: $this->manager->getSessionConfig();

        return ! in_array($config['driver'], [null, 'array']);
    }
}

```  

StartSession中间件大概功能：  
1、先从Session管理器里获取默认的驱动session返回Illuminate\Session\Store实例     
它会根据配置文件的驱动选项保存不同的驱动实例如文件，数据库，cookie等或是说我们前面说过的缓存管理器 redis,memcached        

2、然后返回的这Store它启动【如文件sessionHandler就会读取session文件的内容临时放在Store->attributes【】数组里      
这期间你可以存储任何session数据，删除，修改等操作  

你的session操作是基于Illuminate\Session\Store实例来进行的，它的处理依赖于各种文件，数据库，cookie或是缓存redis,memcache这些驱动  



3、响应结束后它保存数据       

注意事项：  
`$session->setId($request->cookies->get($session->getName()));` 这句话如果说  
你在请求的没传递cookie或是它的值是随机的，不管是文件存储，还是缓存都会生产大量的sessionID  






