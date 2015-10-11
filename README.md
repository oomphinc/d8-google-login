# Drupal 8 Google Login

Allows users to log in to your site using their google account. Creates a new account for a google user. This module requires the `google/apiclient` composer module, specified in this plugin's `composer.json` file.

# Installation

* Clone this repository into your site's `/modules` directory.

* Install the `google/apiclient` composer package in your Drupal 8 site with the following command:

```sh
$ composer require google/apiclient
```

* Create an application in your [google developer console](https://console.developers.google.com/). Set up your OAuth consent screen, then generate a credentials set under "Add Credentials > OAuth 2.0 Client ID", type "Web Application". 

* Enter `http://your-site.com/google-authenticate` into the "Authorized redirect URIs" field, and click "Save".

* Download the client ID JSON file and save with the name `google-oauth-secret.json` into your site's `private` directory, as configured by the `file_private_path` setting in your site's `settings.php`.

* Enable the `google_oauth` module!
