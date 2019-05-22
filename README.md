# PHP SalesForce REST API

## Installation

Via `composer`:

```bash
composer require bizmatesinc/php-salesforce-rest-api
```

## Getting Started

Setting up a Connected App

1. Log into to your SalesForce.org
2. Click on Setup in the upper right-hand menu
3. Under `Build` click `Create > Apps`
4. Scroll to the bottom and click `New` under Connected Apps.
5. Enter the following details for the remote application:
    - Connected App Name
    - API Name
    - Contact Email
    - Enable OAuth Settings under the API dropdown
    - Callback URL
    - Select access scope (If you need a refresh token, specify it here)
6. Click Save

After saving, you will now be given a Consumer Key and Consumer Secret.
Update your config file with values for `consumerKey` and `consumerSecret`.

## Basic usage examples

### Authentication

```php
    $options = [
        'grant_type' => 'password',
        'client_id' => 'CONSUMERKEY',
        'client_secret' => 'CONSUMERSECRET',
        'username' => 'SALESFORCE_USERNAME',
        'password' => 'SALESFORCE_PASSWORD AND SECURITY_TOKEN'
    ];
    
    $salesforce = new bizmatesinc\SalesForce\Authentication\PasswordAuthentication($options);
    $salesforce->authenticate();
    
    $access_token = $salesforce->getAccessToken();
    $instance_url = $salesforce->getInstanceUrl();
```    

Change Endpoint:
    
```php
    $salesforce = new bizmatesinc\SalesForce\Authentication\PasswordAuthentication($options);
    $salesforce->setEndpoint('https://test.salesforce.com/');
    $salesforce->authenticate();
 
    $access_token = $salesforce->getAccessToken();
    $instance_url = $salesforce->getInstanceUrl();
```

### Query

```php
    $query = 'SELECT Id,Name FROM ACCOUNT LIMIT 100';
    
    $crud = new \bizmatesinc\SalesForce\CRUD();
    $crud->query($query);
```

### Create

```php
    
    $data = [
       'Name' => 'some name',
    ];
    
    $crud->create('Account', $data);  #returns id
```

### Update

```php
    $new_data = [
       'Name' => 'another name',
    ];
    
    $crud->update('Account', $id, $new_data); #returns status_code 204
```
### Upsert

```php
    $new_data = [
       'Name' => 'another name',
    ];
    
    $crud->upsert('Account', 'API Name/ Field Name', 'value', $new_data); #returns status_code 204 or 201
```

### Delete

```php
    $crud->delete('Account', $id);
```
