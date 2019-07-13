<?php
/**
 * Created by PhpStorm.
 * User: 1655664358@qq.com
 * Date: 2019/7/13
 * Time: 17:18
 */

$a = [
    'app'                  => [self::class, \Illuminate\Contracts\Container\Container::class, \Illuminate\Contracts\Foundation\Application::class,  \Psr\Container\ContainerInterface::class],
    'auth'                 => [\Illuminate\Auth\AuthManager::class, \Illuminate\Contracts\Auth\Factory::class],
    'auth.driver'          => [\Illuminate\Contracts\Auth\Guard::class],
    'blade.compiler'       => [\Illuminate\View\Compilers\BladeCompiler::class],
    'cache'                => [\Illuminate\Cache\CacheManager::class, \Illuminate\Contracts\Cache\Factory::class],
    'cache.store'          => [\Illuminate\Cache\Repository::class, \Illuminate\Contracts\Cache\Repository::class],
    'config'               => [\Illuminate\Config\Repository::class, \Illuminate\Contracts\Config\Repository::class],
    'cookie'               => [\Illuminate\Cookie\CookieJar::class, \Illuminate\Contracts\Cookie\Factory::class, \Illuminate\Contracts\Cookie\QueueingFactory::class],
    'encrypter'            => [\Illuminate\Encryption\Encrypter::class, \Illuminate\Contracts\Encryption\Encrypter::class],
    'db'                   => [\Illuminate\Database\DatabaseManager::class],
    'db.connection'        => [\Illuminate\Database\Connection::class, \Illuminate\Database\ConnectionInterface::class],
    'events'               => [\Illuminate\Events\Dispatcher::class, \Illuminate\Contracts\Events\Dispatcher::class],
    'files'                => [\Illuminate\Filesystem\Filesystem::class],
    'filesystem'           => [\Illuminate\Filesystem\FilesystemManager::class, \Illuminate\Contracts\Filesystem\Factory::class],
    'filesystem.disk'      => [\Illuminate\Contracts\Filesystem\Filesystem::class],
    'filesystem.cloud'     => [\Illuminate\Contracts\Filesystem\Cloud::class],
    'hash'                 => [\Illuminate\Hashing\HashManager::class],
    'hash.driver'          => [\Illuminate\Contracts\Hashing\Hasher::class],
    'translator'           => [\Illuminate\Translation\Translator::class, \Illuminate\Contracts\Translation\Translator::class],
    'log'                  => [\Illuminate\Log\LogManager::class, \Psr\Log\LoggerInterface::class],
    'mailer'               => [\Illuminate\Mail\Mailer::class, \Illuminate\Contracts\Mail\Mailer::class, \Illuminate\Contracts\Mail\MailQueue::class],
    'auth.password'        => [\Illuminate\Auth\Passwords\PasswordBrokerManager::class, \Illuminate\Contracts\Auth\PasswordBrokerFactory::class],
    'auth.password.broker' => [\Illuminate\Auth\Passwords\PasswordBroker::class, \Illuminate\Contracts\Auth\PasswordBroker::class],
    'queue'                => [\Illuminate\Queue\QueueManager::class, \Illuminate\Contracts\Queue\Factory::class, \Illuminate\Contracts\Queue\Monitor::class],
    'queue.connection'     => [\Illuminate\Contracts\Queue\Queue::class],
    'queue.failer'         => [\Illuminate\Queue\Failed\FailedJobProviderInterface::class],
    'redirect'             => [\Illuminate\Routing\Redirector::class],
    'redis'                => [\Illuminate\Redis\RedisManager::class, \Illuminate\Contracts\Redis\Factory::class],
    'request'              => [\Illuminate\Http\Request::class, \Symfony\Component\HttpFoundation\Request::class],
    'router'               => [\Illuminate\Routing\Router::class, \Illuminate\Contracts\Routing\Registrar::class, \Illuminate\Contracts\Routing\BindingRegistrar::class],
    'session'              => [\Illuminate\Session\SessionManager::class],
    'session.store'        => [\Illuminate\Session\Store::class, \Illuminate\Contracts\Session\Session::class],
    'url'                  => [\Illuminate\Routing\UrlGenerator::class, \Illuminate\Contracts\Routing\UrlGenerator::class],
    'validator'            => [\Illuminate\Validation\Factory::class, \Illuminate\Contracts\Validation\Factory::class],
    'view'                 => [\Illuminate\View\Factory::class, \Illuminate\Contracts\View\Factory::class],
];
$abstractAliasesA = [];
$aliasesA = [];
foreach($a as $key => $aliases) {
    foreach ($aliases as $alias) {
        $aliasesA[$alias] = $key;

        $abstractAliasesA[$key][] = $alias;
    }
}

print_r($abstractAliasesA);