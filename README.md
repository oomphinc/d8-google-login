# Drupal 8 Google Login

Allows users to log in to your site using their google account. Creates a new account for a google user.

# Installation

1. Install the Google API client library.

This module requires the `google/apiclient` composer module, specified in this plugin's `composer.json` file. Include `google/apiclient` in your Drupal 8 site with the following command:

```sh
$ composer require google/apiclient
```

2. Create an application in your [google developer console](https://console.developers.google.com/). Set up your OAuth consent screen, then generate a credentials set under "Add Credentials > OAuth 2.0 Client ID", type "Web Application". 

3. Enter `http://your-site.com/google-authenticate` into the "Authorized redirect URIs" field, and click "Save".

4. Download the client ID JSON file and save with the name `google-oauth-secret.json` into your site's `private` directory, as configured by the `file_private_path` setting in your site's `settings.php`.

5. Enable the `google_oauth` module!
