# App installation helper for Golden Planet Platform


## Configuration add to 

### config.yml
```yaml
golden_planet_gpp_app:
    api:
        app_key: '%env(API_KEY)%'
        app_secret: '%env(API_SECRET)%'
        app_scope: '%env(API_SCOPE)%'
    app:
        redirect_url: '%env(REDIRECT_URL)%'
        uninstall_url: '%env(UNINSTALL_URL)%'
```
### routing.yml
```yaml
gpp_app:
    resource: "@GoldenPlanetGPPAppBundle/Resources/config/routing.yml"
    prefix:   /
```

### AppKernel.php

```php
    new GoldenPlanet\GPPAppBundle\GoldenPlanetGPPAppBundle(),
```

### 
    env(API_KEY):
    env(API_SECRET):
    env(API_SCOPE):
    env(REDIRECT_URL): http://obb.docker:8888/app/gpp/oauth/authorize
    env(UNINSTALL_URL): http://obb.docker:8888/app/gpp/oauth/unauthorize

### security.yml

```yaml
    firewalls:

        app-install:
            pattern:  ^/app/gpp/authorize
            stateless: true
            anonymous: true
        
        secured_area:
            pattern:    ^/
            stateless: false
            simple_preauth:
                authenticator: GoldenPlanet\GPPAppBundle\Security\HmacAuthenticator
            provider: store
            
    access_control:

        - { path: ^/app/gpp/authorize,     roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/,  roles: IS_AUTHENTICATED_FULLY  }

```
