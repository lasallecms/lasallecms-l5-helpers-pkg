# INSTALLATION

## Summary 
My Flagship CMS app so depends on this package that it has the composer and service provider stuff built in already. 


## composer.json:

```
{
    "require": {
        "lasallecms/helpers": "0.1.*",
    }
}
```


## Service Provider

In config/app.php:
```
Lasallecms\Helpers\HelpersServiceProvider::class,
```


## Facade Alias

In config/app.php:
```
'LaSalleHelpers'      => 'Lasallecms\Helpers\HelperfFacade',
```


## Dependencies
* none


## Publish the Package's Config

n/a

## Migration

n/a

## Notes

n/a


## Serious Caveat 

This package is designed to run specifically with my Flagship blog app.