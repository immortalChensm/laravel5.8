### view层  
[首页](../readme.md) [下一页：](view.md)  [上一页：数据库DB和模型类实现流程](db.md)  

- view服务提供类注册  
    这个不用了吧，前面已经说过了，所有的服务类会自动注册到Application里保存，就是为了方便你下次用时取出来  
    ![view](app.md)  
    
    使用  
    `return view("admin.index",compact('data'));`  
    
    view函数  
    ```php  
    function view($view = null, $data = [], $mergeData = [])
        {
        //这个怎么来的，自己去看前面的说明
            $factory = app(ViewFactory::class);
    
            if (func_num_args() === 0) {
                return $factory;
            }
    
            return $factory->make($view, $data, $mergeData);
        }
    ```  
    
    app(ViewFactory::class)取得的函数返回如下【怎么知道的？就是View服务提供类运行时自动注册进来的】    
    ```php  
    function ($app) {
           //Illuminate\View\EngineResolver;
    
         $resolver = $app['view.engine.resolver'];
         //Illuminate\View\FileViewFinder
         $finder = $app['view.finder'];
         /**
          protected function createFactory($resolver, $finder, $events)
             {
             //Illuminate\View\Factory
                 return new Factory($resolver, $finder, $events);
             }
         **/
         $factory = $this->createFactory($resolver, $finder, $app['events']);
         $factory->setContainer($app);
         $factory->share('app', $app);
         return $factory;
     }
    ```  
    ` $resolver = $app['view.engine.resolver'];`返回的匿名函数  
    ```php  
    function () {
         \\Illuminate\View
         $resolver = new EngineResolver;
         //实例引擎
         foreach (['file', 'php', 'blade'] as $engine) {
         //运行下面指定的方法
             $this->{'register'.ucfirst($engine).'Engine'}($resolver);
         }
         
         /**
             function registerFileEngine($resolver)
             {  
             //保存文件引擎 
                 $resolver->register('file', function () {
                     return new FileEngine;
                 });
             }
                
             //保存php引擎
             function registerPhpEngine($resolver)
             {
                 $resolver->register('php', function () {
                     return new PhpEngine;
                 });
             }
             //保存blade引擎【Application也保存了一份】
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
     }
    ```  
    
    EngineResolver类  
    ```php  
     public function register($engine, Closure $resolver)
        {
            unset($this->resolved[$engine]);
    
            $this->resolvers[$engine] = $resolver;
        }  
    ```  
    
    `$finder = $app['view.finder'];`返回的匿名函数  
    ```php  
    function ($app) {
            \\Illuminate\View 
            //文件系统对象，视图文件路径
            return new FileViewFinder($app['files'], $app['config']['view.paths']);
        }
    ```  
    
    视图工厂类  
    ```php  
    
    namespace Illuminate\View;
    
    use Illuminate\Support\Arr;
    use Illuminate\Support\Str;
    use InvalidArgumentException;
    use Illuminate\Support\Traits\Macroable;
    use Illuminate\Contracts\Events\Dispatcher;
    use Illuminate\Contracts\Support\Arrayable;
    use Illuminate\View\Engines\EngineResolver;
    use Illuminate\Contracts\Container\Container;
    use Illuminate\Contracts\View\Factory as FactoryContract;
    
    class Factory implements FactoryContract
    {
        use Macroable,
            Concerns\ManagesComponents,
            Concerns\ManagesEvents,
            Concerns\ManagesLayouts,
            Concerns\ManagesLoops,
            Concerns\ManagesStacks,
            Concerns\ManagesTranslations;
    
        /**
         * The engine implementation.
         *
         * @var \Illuminate\View\Engines\EngineResolver
         */
        protected $engines;
    
        /**
         * The view finder implementation.
         *
         * @var \Illuminate\View\ViewFinderInterface
         */
        protected $finder;
    
        /**
         * The event dispatcher instance.
         *
         * @var \Illuminate\Contracts\Events\Dispatcher
         */
        protected $events;
    
        /**
         * The IoC container instance.
         *
         * @var \Illuminate\Contracts\Container\Container
         */
        protected $container;
    
        /**
         * Data that should be available to all templates.
         *
         * @var array
         */
        protected $shared = [];
    
        /**
         * The extension to engine bindings.
         *
         * @var array
         */
        protected $extensions = [
            'blade.php' => 'blade',
            'php' => 'php',
            'css' => 'file',
            'html' => 'file',
        ];
    
        /**
         * The view composer events.
         *
         * @var array
         */
        protected $composers = [];
    
        /**
         * The number of active rendering operations.
         *
         * @var int
         */
        protected $renderCount = 0;
    
        /**
         * Create a new view factory instance.
         *
         * @param  \Illuminate\View\Engines\EngineResolver  $engines
         * @param  \Illuminate\View\ViewFinderInterface  $finder
         * @param  \Illuminate\Contracts\Events\Dispatcher  $events
         * @return void
         */
        public function __construct(EngineResolver $engines, ViewFinderInterface $finder, Dispatcher $events)
        {

            $this->finder = $finder;//Illuminate\View\FileViewFinder
            $this->events = $events;
            $this->engines = $engines;//Illuminate\View\EngineResolver;
    
            $this->share('__env', $this);
        }

    ```  
    所以$factory = app(ViewFactory::class);返回的是Illuminate\View\Factory类的实例对象  
    
    下一句`return $factory->make($view, $data, $mergeData);`的流程  
    ```php  
    //$view=视图文件路径 admin.view 
    $data=数据【数据可能是Collection集合对象】
    public function make($view, $data = [], $mergeData = [])
        {
        //得到模板文件的完整地址
            $path = $this->finder->find(
                $view = $this->normalizeName($view)
            );
            //数据处理
            $data = array_merge($mergeData, $this->parseData($data));
            //$this->viewInstance($view, $path, $data)
            return tap($this->viewInstance($view, $path, $data), function ($view) {
                $this->callCreator($view);
            });
        }
    ```     
    
    ```php  
    
        $path = $this->finder->find(
            $view = $this->normalizeName($view)
        );  
        
    Illuminate\View\FileViewFinder->find()方法
    public function find($name)
        {
            if (isset($this->views[$name])) {
                return $this->views[$name];
            }
    
            if ($this->hasHintInformation($name = trim($name))) {
                return $this->views[$name] = $this->findNamespacedView($name);
            }
            //得到admin.index=模板文件的完整路径地址
            return $this->views[$name] = $this->findInPaths($name, $this->paths);
        }
    ```    
    
    ```php  
    protected function viewInstance($view, $path, $data)
        {
            return new View($this, $this->getEngineFromPath($path), $view, $path, $data);
        }
    
    
    public function getEngineFromPath($path)
        {
        //根据模板文件后缀判断是否存在
            if (! $extension = $this->getExtension($path)) {
                throw new InvalidArgumentException("Unrecognized extension in file: {$path}");
            }
            //得到php如果定义的index.blade.php
            $engine = $this->extensions[$extension];
    
            return $this->engines->resolve($engine);
        }
        
    public function resolve($engine)
        {
            if (isset($this->resolved[$engine])) {
                return $this->resolved[$engine];
            }
    
            if (isset($this->resolvers[$engine])) {
                return $this->resolved[$engine] = call_user_func($this->resolvers[$engine]);
            }
    
            throw new InvalidArgumentException("Engine [{$engine}] not found.");
        }
        
    
    protected function getExtension($path)
        {
        //得到数组
        /**
        $extensions = [
                'blade.php' => 'blade',
                'php' => 'php',
                'css' => 'file',
                'html' => 'file',
            ];
        **/
            $extensions = array_keys($this->extensions);
            
            return Arr::first($extensions, function ($value) use ($path) {
                return Str::endsWith($path, '.'.$value);
            });
        }
    ```
    
    
    
