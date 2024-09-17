<?php

namespace StarkCore;
use StarkCore\Utils\Checks;


class MarketplaceApp extends User
{

    public $authorizationId;

    /**
    # MarketPlaceApp object
    The MarketPlaceApp object is an authentication entity for the SDK that
    represents your MarketPlace Application, being able to access any authorized Workspace.
    All requests to the Stark Bank and Stark Infra API must be authenticated via an SDK user,
    which must have been previously created at the Stark Bank or Stark Infra websites
    [https://web.sandbox.starkbank.com] or [https://web.starkbank.com]
    before you can use it in this SDK. MarketplaceApps may be passed as the user parameter on
    each request or may be defined as the default user at the start (See README).
    If you are accessing a specific MarketplaceAppAuthorization using MarketplaceApp credentials, you should
    specify the authorization ID when building the MarketplaceApp object or by request, using
    the MarketplaceApp.replace(app, authorizationId) function, which creates a copy of the app
    object with the altered authorization ID. If you are listing authorizations, the
    authorizationId should be None.

    ## Parameters (required):
        - id [string]: unique id required to identify the app. ex: "mycompany.myapp"
        - private_key [EllipticCurve.PrivateKey()]: PEM string of the private key linked to the app. ex: "-----BEGIN PUBLIC KEY-----\nMFYwEAYHKoZIzj0CAQYFK4EEAAoDQgAEyTIHK6jYuik6ktM9FIF3yCEYzpLjO5X/\ntqDioGM+R2RyW0QEo+1DG8BrUf4UXHSvCjtQ0yLppygz23z0yPZYfw==\n-----END PUBLIC KEY-----"
        - environment [string]: environment where the app is being used. ex: "sandbox" or "production"
        - authorizationId [string]: unique id of the accessed MarketplaceAppAuthorization, if any. ex: None or "4848484848484848"
    
    ## Attributes (return-only):
        - pem [string]: private key in pem format. ex: "-----BEGIN PUBLIC KEY-----\nMFYwEAYHKoZIzj0CAQYFK4EEAAoDQgAEyTIHK6jYuik6ktM9FIF3yCEYzpLjO5X/\ntqDioGM+R2RyW0QEo+1DG8BrUf4UXHSvCjtQ0yLppygz23z0yPZYfw==\n-----END PUBLIC KEY-----"
    */
    function __construct(array $params)
    {
        parent::__construct($params);

        $this->authorizationId = Checks::checkParam($params, "authorizationId");

        Checks::checkParams($params);
    }

    public function accessId()
    {
        if ($this->authorizationId)
            return "marketplace-app-authorization/" . $this->authorizationId;
        return "marketplace-app/" . $this->id;
    }

    public static function replace($marketplaceApp, $authorizationId)
    {
        return new MarketplaceApp([
            "id" => $marketplaceApp->id,
            "environment" => $marketplaceApp->environment,
            "privateKey" => $marketplaceApp->pem,
            "authorizationId" => $authorizationId
        ]);
    }
}
