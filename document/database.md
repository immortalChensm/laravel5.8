### Illuminate\Database\DatabaseManager运行时数据的存储状态  
```php  
/**
    
    protected $app;

    /**
     * The database connection factory instance.
     *
     * @var \Illuminate\Database\Connectors\ConnectionFactory
     */
    protected $factory;

    /**
     * The active connection instances.
     * 数据库连接实例
     * @var array
     */
    protected $connections = [];

    /**
     * The custom connection resolvers.
     *
     * @var array
     */
    protected $extensions = [];

    /**
     * The callback to be executed to reconnect to a database.
     *
     * @var callable
     */
    protected $reconnector;

```