<?php


use Doctrine\DBAL\Types\Type;

require_once __DIR__ . '/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
 */

$app = new Laravel\Lumen\Application(
    realpath(__DIR__ . '/../')
);

$app->withFacades();
$app->withEloquent();

$app['path.public'] = base_path('public');
$app['path.config'] = base_path('config');
$app['path.storage'] = base_path('storage');
/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
 */

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(Faker\Generator::class, function () {
    return Faker\Factory::create('nl_BE');
});

/*
|--------------------------------------------------------------------------
| Register Repository Bindings
|--------------------------------------------------------------------------
|
 */

$repos = [
    'Identity\Student',
    'Identity\Group',
    'Identity\Staff',
    'Identity\Role',
    'Education\Branch',
    'Evaluation\Evaluation',
    'Events\EventTracking',
];

foreach ($repos as $idx => $repo) {
    $app->bind(
        'App\Domain\Model\\' . $repo . 'Repository',
        'App\Repositories\\' . $repo . 'DoctrineRepository'
    );
}

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'llbeassignedtosomespecificroutes .
|
 */
$app->middleware([
    \Neomerx\CorsIlluminate\CorsMiddleware::class,
]);
// $app->middleware([
//    App\Http\Middleware\ExampleMiddleware::class
// ]);

// $app->routeMiddleware([
//     'auth' => App\Http\Middleware\Authenticate::class,
// ]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
 */

$app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

//class_alias('Laravel\Socialite\Facades\Socialite', 'Socialite');


$app->register(Laravel\Socialite\SocialiteServiceProvider::class);
$app->register(\Neomerx\CorsIlluminate\Providers\LumenServiceProvider::class);
$app->register(LaravelDoctrine\ORM\DoctrineServiceProvider::class);


//class_alias('LaravelDoctrine\ORM\Facades\EntityManager', 'EntityManager');
//class_alias('LaravelDoctrine\ORM\Facades\Registry', 'Registry');
//class_alias('LaravelDoctrine\ORM\Facades\Doctrine', 'Doctrine');
//class_alias('Webpatser\Uuid\Uuid', 'Uuid');

$app->singleton(JMS\Serializer\Serializer::class, function ($app) {

    /** @var \Illuminate\Config\Repository $config */
    $config = $app->make('Illuminate\Config\Repository');


    return JMS\Serializer\SerializerBuilder
        ::create()
        ->setCacheDir(storage_path('cache/serializer'))
        ->setDebug($config->get('app.debug'))
        ->setPropertyNamingStrategy(new JMS\Serializer\Naming\SerializedNameAnnotationStrategy(new JMS\Serializer\Naming\IdenticalPropertyNamingStrategy()))
        ->addDefaultHandlers()
        ->build();
});
$app->bind(JMS\Serializer\SerializerInterface::class, JMS\Serializer\Serializer::class);
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');

/*
|--------------------------------------------------------------------------
| Load Doctrine Custom Types
|--------------------------------------------------------------------------
|
| When you have implemented the type you still need to let Doctrine know about it.
| This can be achieved through the Doctrine\DBAL\Types\Type#addType($name, $className) method.
|
 */
if (!Type::hasType('gender')) {
    Type::addType('gender', \App\Domain\Model\Identity\EnumGenderType::class);
}
if (!Type::hasType('stafftype')) {
    Type::addType('stafftype', \App\Domain\Model\Identity\EnumStaffType::class);
}
if (!Type::hasType('evaluationtype')) {
    Type::addType('evaluationtype', \App\Domain\Model\Evaluation\EnumEvaluationType::class);
}
if (!Type::hasType('redicoditype')) {
    Type::addType('redicoditype', \App\Domain\Model\Education\EnumRedicodiType::class);
}

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
 */

$app->group(['namespace' => 'App\Http\Controllers'], function ($app) {
    require __DIR__ . '/../app/Http/routes.php';
});

return $app;
