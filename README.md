# Php Salesforce Rest Api

```Bijesh Shrestha``` ```bjsmasth``` ```bjsmasth@gmail.com``` ```bjsmasth``` ```php rest api```

## Install

Via Composer

Add following repository in composer.json
``` bash
"repositories": [
         {
             "type": "git",
             "url": "git@github.com:bjsmasth/php-salesforce-rest-api.git"
         },
         ..........
         ..........
    ]
```
``` bash
$php composer.phar require php-salesforce-rest-api
```

# Getting Started

Setting up a Connected App

1. Log into to your Salesforce org
2. Click on Setup in the upper right-hand menu
3. Under Build click ```Create > Apps ```
4. Scroll to the bottom and click ```New``` under Connected Apps.
5. Enter the following details for the remote application:
    - Connected App Name
    - API Name
    - Contact Email
    - Enable OAuth Settings under the API dropdown
    - Callback URL
    - Select access scope (If you need a refresh token, specify it here)
6. Click Save

After saving, you will now be given a Consumer Key and Consumer Secret. Update your config file with values for ```consumerKey``` and ```consumerSecret```

# Setup

Authenticate APP

```bash
    $options = [
        'grant_type' => 'password',
        'client_id' => 'CONSUMERKEY',
        'client_secret' => 'CONSUMERSECRET',
        'username' => 'SALESFORCE_USERNAME',
        'password' => 'SALESFORCE_PASSWORD AND SECURITY_TOKEN'
    ];
    
    $salesforce = new bjsmasth\Salesforce\Authentication\PasswordAuthentication($options);
    $salesforce->authenticate();
    
    $access_token = $salesforce->getAccessToken();
    $instance_url = $salesforce->getInstanceUrl();
    
    Change Endpoint
    
    $salesforce = new bjsmasth\Salesforce\Authentication\PasswordAuthentication($options);
    $salesforce->setEndpoint('https://test.salesforce.com/');
    $salesforce->authenticate();
 
    $access_token = $salesforce->getAccessToken();
    $instance_url = $salesforce->getInstanceUrl();
```