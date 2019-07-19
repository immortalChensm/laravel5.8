### 事件调度器  
Illuminate\Events\Dispatcher  
```php  
    listeners=[
    //事件名称=>监听器
        'eloquent.created: App\User'=>function ($event, $payload) use ($listener, $wildcard) {
                                                  if ($wildcard) {
                                                      return call_user_func([$listener实例,method=handle], $event, $payload);
                                                  }
                                      
                                                  return call_user_func_array(
                                                      [$listener实例,method=handle], $payload
                                                  );
                                              },
                                              
        //如果监听器是函数则是
        'eloquent.created: App\User'=>function ($event, $payload) use ($listener, $wildcard) {
                    if ($wildcard) {
                        return $listener($event, $payload);
                    }
        
                    return $listener(...array_values($payload));
                }
    ]   
```