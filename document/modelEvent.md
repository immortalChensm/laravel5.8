### **模型事件** 

- App服务提供者注册服务   
    ```php  
    namespace App\Providers;
    
    use App\Observers\UserObserver;
    use App\User;
    use Illuminate\Support\ServiceProvider;
    
    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Register any application services.
         *
         * @return void
         */
        public function register()
        {
            //你可以在本类中做一些事件，如注册一些内容到容器中，然后你就可以在框架的任何地方取出来使用
           
            
        }
    
        /**
         * Bootstrap any application services.
         *
         * @return void
         */
        public function boot()
        {
            //
            User::observe(UserObserver::class);
        }
    }
    ```  
    
    Model->observe
    ```php
    public static function observe($classes)
        {
            $instance = new static;//实例本类
              //Arr::wrap($classes)封装为数组
            foreach (Arr::wrap($classes) as $class) {
                $instance->registerObserver($class);
            }
        }
      
    protected function registerObserver($class)
        {
          //获取类名
            $className = $this->resolveObserverClassName($class);
              //返回可观察的事件数组
            foreach ($this->getObservableEvents() as $event) {
          //判断观察者是否有指定的事件方法
                if (method_exists($class, $event)) {
                    static::registerModelEvent($event, $className.'@'.$event);
                }
            }
        }
     
    //$event 事件方法名
    //$className.'@'.$event 观察者类名@事件方法名
    protected static function registerModelEvent($event, $callback)
        {
            if (isset(static::$dispatcher)) {
                $name = static::class;
                  //事件注册
                //eloquent.created[假设].当前模型子类名
                static::$dispatcher->listen("eloquent.{$event}: {$name}", $callback);
            }
        }
    public function getObservableEvents()
        {
            return array_merge(
                [
                    'retrieved', 'creating', 'created', 'updating', 'updated',
                    'saving', 'saved', 'restoring', 'restored', 'replicating',
                    'deleting', 'deleted', 'forceDeleted',
                ],
                $this->observables
            );
        }
      
    private function resolveObserverClassName($class)
        {
            if (is_object($class)) {
                return get_class($class);
            }
    
            if (class_exists($class)) {
                return $class;
            }
    
            throw new InvalidArgumentException('Unable to find observer: '.$class);
        }
    ```  
    
    模型插入数据时触发的事件  
    ```php  
     public function save(array $options = [])
        {
            $query = $this->newModelQuery();
    
            // If the "saving" event returns false we'll bail out of the save and return
            // false, indicating that the save failed. This provides a chance for any
            // listeners to cancel save operations if validations fail or whatever.
            if ($this->fireModelEvent('saving') === false) {
                return false;
            }
    
            // If the model already exists in the database we can just update our record
            // that is already in this database using the current IDs in this "where"
            // clause to only update this model. Otherwise, we'll just insert them.
            if ($this->exists) {
                $saved = $this->isDirty() ?
                            $this->performUpdate($query) : true;
            }
    
            // If the model is brand new, we'll insert it into our database and set the
            // ID attribute on the model to the value of the newly inserted row's ID
            // which is typically an auto-increment value managed by the database.
            else {
                $saved = $this->performInsert($query);
    
                if (! $this->getConnectionName() &&
                    $connection = $query->getConnection()) {
                    $this->setConnection($connection->getName());
                }
            }
    
            // If the model is successfully saved, we need to do a few more things once
            // that is done. We will call the "saved" method here to run any actions
            // we need to happen after a model gets successfully saved right here.
            if ($saved) {
                $this->finishSave($options);
            }
    
            return $saved;
        }
        
     protected function fireModelEvent($event, $halt = true)
         {
             if (! isset(static::$dispatcher)) {
                 return true;
             }
     
             // First, we will get the proper method to call on the event dispatcher, and then we
             // will attempt to fire a custom, object based event for the given event. If that
             // returns a result we can return that result, or we'll call the string events.
             $method = $halt ? 'until' : 'dispatch';
     
             $result = $this->filterModelEventResults(
                 $this->fireCustomModelEvent($event, $method)
             );
             $b = $result;
     
             if ($result === false) {
                 return false;
             }
             //事件调度运行
             return ! empty($result) ? $result : static::$dispatcher->{$method}(
                 "eloquent.{$event}: ".static::class, $this
             );
             /**
             App\Observers\UserObserver->saving(User $user)
                 {
                     //
                     echo "saving".PHP_EOL;
                 }
             **/
         }
         
     protected function fireCustomModelEvent($event, $method)
         {
             if (! isset($this->dispatchesEvents[$event])) {
                 return;
             }
     
             $result = static::$dispatcher->$method(new $this->dispatchesEvents[$event]($this));
     
             if (! is_null($result)) {
                 return $result;
             }
         }
    ```  
    Illuminate\Events\Dispatcher
    事件注册   
    1、事件注册时，监听器是个类名时则是封装成一个匿名返回存在事件池里 listeners【事件名称】【】=function(参数){return call_user_func(【监听器类实例，方法】，参数)};
    ```php  
    //$events=eloquent.created:App\User
    //$listener=App\Observers\UserObserver@created
    public function listen($events, $listener)
        {
            foreach ((array) $events as $event) {
                //判断事件名称是否含有*号
                if (Str::contains($event, '*')) {
                    $this->setupWildcardListen($event, $listener);
                } else {
                //listeners[eloquent.created:App\User][]=function(){call_user_func_arrya([$listener类实例,方法名默认为handle],$payload参数)}
                    $this->listeners[$event][] = $this->makeListener($listener);
                }
            }
        }  
    //$listener=$listener=App\Observers\UserObserver@created
    public function makeListener($listener, $wildcard = false)
        {
            if (is_string($listener)) {
            //监听器是个字符串情况时 封装成匿名函数返回
                return $this->createClassListener($listener, $wildcard);
            }
            //监听器不是字符串时 
            //把监听器当做匿名函数了
            return function ($event, $payload) use ($listener, $wildcard) {
                if ($wildcard) {//参数为值，在调用的时候会把事件也一起传递进来
                    return $listener($event, $payload);
                }
    
                return $listener(...array_values($payload));
            };
        }
    //$listener=$listener=App\Observers\UserObserver@created
    //$wildcard=false
    1、将监听类实例化并拼装它的方法构成数组供call_user_func_array函数调用   
    2、封装成匿名函数返回 
    
    public function createClassListener($listener, $wildcard = false)
        {   
            //封装一个匿名函数【当使用时运行此匿名函数】
            return function ($event, $payload) use ($listener, $wildcard) {
                if ($wildcard) {
                    return call_user_func($this->createClassCallable($listener), $event, $payload);
                }
    
                return call_user_func_array(
                //$this->createClassCallable($listener) 根据类名并实例化返回
                //[类的实例,类的方法默认方法是handle方法]
                //$payload 方法的参数【在调用时可以传递进来】
                    $this->createClassCallable($listener), $payload
                );
            };
        }  
        
    protected function createClassCallable($listener)
        {
        //分到本类的类名和方法名，没有方法名，默认就是handle方法
            [$class, $method] = $this->parseClassCallable($listener);
            //检测类名是否是属于队列类【你创建的任务就是继承的队列】
            if ($this->handlerShouldBeQueued($class)) {
                return $this->createQueuedHandlerCallable($class, $method);
            }
            //实例化类得到实例，并组装实例和它的方法名返回
            return [$this->container->make($class), $method];
        }
        
    protected function handlerShouldBeQueued($class)【检测是否属于队列类】
        {
            try {
                return (new ReflectionClass($class))->implementsInterface(
                    ShouldQueue::class
                );
            } catch (Exception $e) {
                return false;
            }
        }
    protected function parseClassCallable($listener)【将类名拆分成类名和方法名数组返回】
        {
            //将$listener拆开成数组返回【分别是类名，方法名，如果此$listener是class@method就会拆开，否则则返回
            //className@handle
            return Str::parseCallback($listener, 'handle');
        }  
        
    通配符事件池 
     protected function setupWildcardListen($event, $listener)
        {
            $this->wildcards[$event][] = $this->makeListener($listener, true);
    
            $this->wildcardsCache = [];
        }
    ```  
    
    事件调度运行  
    ```php 
    //$event事件名称 
    //$payload=参数
    public function dispatch($event, $payload = [], $halt = false)
        {
            //如果$event是对象，则
            //$event是类实例数组
            //$payload是类名 
            
            //如果$event不是对象
            //$event不变
            //$payload=则是参数数组
            [$event, $payload] = $this->parseEventAndPayload(
                $event, $payload
            );
            //判断参数$pyalod[0]存在且属于ShouldBroadcast时【暂时先不管】
            if ($this->shouldBroadcast($payload)) {
                $this->broadcastEvent($payload[0]);
            }
            $responses = [];
    
            foreach ($this->getListeners($event) as $listener) { 
            
            //得到事件对应的匿名函数
            //运行匿名函数
                $response = $listener($event, $payload);
    
                if ($halt && ! is_null($response)) {
                    return $response;
                }

                if ($response === false) {
                    break;
                }
    
                $responses[] = $response;
            }
    
            return $halt ? null : $responses;
        }
    public function getListeners($eventName)
        {
            //从事件池里取出对应事件的匿名函数
            $listeners = $this->listeners[$eventName] ?? [];
            //如果这个通配事件池有缓存的事件，则获取并缓存再返回合并
            $listeners = array_merge(
                $listeners,
                $this->wildcardsCache[$eventName] ?? $this->getWildcardListeners($eventName)
            );
            //判断事件名称类是否存在且不触发自动加载
            return class_exists($eventName, false)
                        ? $this->addInterfaceListeners($eventName, $listeners)
                        : $listeners;
        }
    protected function getWildcardListeners($eventName)
        {
            $wildcards = [];
            //通配符事件池
            foreach ($this->wildcards as $key => $listeners) {
            //判断每个事件名称是否属于当前传递进来的事件名称 
                if (Str::is($key, $eventName)) {
                //将事件对应的匿名函数保存并合并成数组
                    $wildcards = array_merge($wildcards, $listeners);
                }
            }
            //将事件名称=匿名函数缓存并返回
            return $this->wildcardsCache[$eventName] = $wildcards;
        }
        
    protected function addInterfaceListeners($eventName, array $listeners = [])
        {
        //返回事件类名所继承的接口类
            foreach (class_implements($eventName) as $interface) {
            //事件池里是否存在相应的数据
                if (isset($this->listeners[$interface])) {
                
                    foreach ($this->listeners[$interface] as $names) {
                        $listeners = array_merge($listeners, (array) $names);
                    }
                }
            }
    
            return $listeners;
        }
        
    protected function parseEventAndPayload($event, $payload)【返回事件名称，事件对象，参数】
        {
        //事件名称是对象情况 
            if (is_object($event)) {
            //$payload=事件对象数组
            //$event=事件类名称 
                [$payload, $event] = [[$event], get_class($event)];
            }
            //事件名称，参数数组
            return [$event, Arr::wrap($payload)];
        }
        
    protected function shouldBroadcast(array $payload)
        {
            return isset($payload[0]) &&
                   $payload[0] instanceof ShouldBroadcast &&
                   $this->broadcastWhen($payload[0]);
        }
    ```  
    
    模型事件实现基于事件Dispatcher来实现的，注册和调度【把匿名函数扔数组里保存，再从数组里取现来运行】  
    
    可以在任何服务提供类添加如下代码   
    XXXServiceProvider->register/boot   
    ```php  
      $this->app['events']->listen("装逼",function ($request){
                            echo "老子就是想装逼而已";
                            echo $request['name'];
                        });
                        
    ```     
    控制器内容   
    ```php  
    <?php
    
    namespace App\Http\Controllers\Admin;
    use App\Http\Controllers\Controller;
    class TestController extends Controller
    {
        function index()
        {
            print_r(app('events')->dispatch("装逼",app()['request']));
            return response()->json(['a']);
    
        }
    }
    ```  
    
    以上就是事件注册和调度的使用【可以使用类】   
    