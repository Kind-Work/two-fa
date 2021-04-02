<?php

namespace KindWork\TwoFa;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
  protected $scripts = [__DIR__ . '/../dist/js/scripts.js'];

  protected $stylesheets = [__DIR__ . '/../dist/css/styles.css'];

  protected $routes = [
    'cp' => __DIR__ . '/../routes/cp.php',
  ];

  protected $middlewareGroups = [
    'statamic.cp.authenticated' => [Middleware\CheckTwoFa::class],
  ];

  protected $fieldtypes = [Fieldtypes\TwoFaFieldtype::class];

  public function boot()
  {
    parent::boot();
    $this->bootAddonViews()->bootAddonTranslations();
  }

  protected function bootAddonTranslations()
  {
    $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'twofa');
    return $this;
  }

  protected function bootAddonViews()
  {
    $this->loadViewsFrom(__DIR__ . '/../resources/views', 'twofa');
    $this->publishes(
      [__DIR__ . '/../resources/views' => resource_path('views/vendor/twofa')],
      'twofa-views'
    );
    return $this;
  }
}
