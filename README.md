# Shift8 Zoom Webinar
* Contributors: shift8
* Donate link: https://www.shift8web.ca
* Tags: zoom, webinar, shift8, import
* Requires at least: 3.0.1
* Tested up to: 5.5
* Stable tag: 1.0.14
* License: GPLv3
* License URI: http://www.gnu.org/licenses/gpl-3.0.html

This is a plugin that integrates into your Zoom account and will facilitate importing your Zoom webinars into custom content on a schedule that you define. 

## Instructions for setup 

1. Register or set up your zoom account
2. Once your account is activated, go to App marketplace and click "build app"
3. Create a JWT app and copy down the API key and API secret
6. Install this Wordpress plugin and activate
7. Go to the plugin settings page (Shift8 > Zoom Settings) and enter the API key and API secret and then click "Save Changes"
8. Once saved, you can click the "Check" button to ensure we can connect to the Zoom API successfully

## Want to see the plugin in action?

You can view three example sites where this plugin is live :

- Example Site 1 : [Wordpress Hosting](https://www.stackstar.com "Wordpress Hosting")
- Example Site 2 : [Web Design in Toronto](https://www.shift8web.ca "Web Design in Toronto")

## Features

- Integrate with your Zoom account to pull webinars
- Imports webinars at a schedule that you choose into custom content that is automatically generated by the plugin

## Installation

This section describes how to install the plugin and get it working.

e.g.

1. Upload the plugin files to the `/wp-content/plugins/shif8-zoom` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to the plugin settings page and define your settings

## Frequently Asked Questions 

### I tested it on my site and its not working for me!

Visit the support forums here and let us know. We will try our best to help!

## Screenshots 

1. Main settings page of plugin admin

## Changelog 

### 1.0.0
* Stable version created

### 1.0.1
* Force flushing of rewrite rules once when custom post type is created

### 1.0.2
* Added language dropdown, initial import will set english as default language, added new options for type

### 1.0.3
* Added input option to add text string to filter webinar titles during import. User can add a string of text to scan during import process to filter webinars from being imported.

### 1.0.4
* Added some custom types for webinar custom field 

### 1.0.5
* Show categories in post list

### 1.0.6
* Full webinar agenda import now, separate api query

### 1.0.7
* Adjust post type slug to events

### 1.0.8
* No longer filtering html on agenda details

### 1.0.9
* Comparing zoom IDs instead of UUID to avoid duplicates

### 1.0.10
* Increase number of webinars to import to 300. Todo : paginate results if above 300.

### 1.0.11
* Correctly importing webinars as UTC first then converting to the timezone set for each webinar

### 1.0.12
* Translating long form timezone from zoom to abbreviated ("EST") format

### 1.0.13
* Importing registration_url for join url link instead of join_url

### 1.0.14
* Remove lang dropdown, fully handled by WPML where applicable or used