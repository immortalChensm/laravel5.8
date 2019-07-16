### Illuminate\Database\Query\Builder查询构建器成员  
```php  

    public $connection;

    public $grammar;

   
    public $processor;

    public $bindings = [
        'select' => [],
        'from'   => [],
        'join'   => [],
        'where'  => [],
        'having' => [],
        'order'  => [],
        'union'  => [],
    ];

    public $aggregate;

    
    public $columns=['*'];

   
    public $distinct = false;

   
    public $from;

    
    public $joins;

    
    public $wheres = [];

   
    public $groups;

    
    public $havings;

   
    public $orders;

   
    public $limit;

    
    public $offset;

    
    public $unions;

    
    public $unionLimit;

    
    public $unionOffset;

    
    public $unionOrders;

    
    public $lock;

   
    public $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
        'like', 'like binary', 'not like', 'ilike',
        '&', '|', '^', '<<', '>>',
        'rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', 'not ilike', '~~*', '!~~*',
    ];

   
    public $useWritePdo = false;

```