=== Plugin Name ===
Contributors:      Payjunction
Plugin Name:       WooCommerce PayJunction Gateway
Plugin URI:        http://www.payjunction.com
Tags:              payjunction,woothemes,woocommerce,payment gateway,payment,module,ecommerce,online payments,
Author URI:        http://www.payjunction.com
Author:            PayJunction
Donate link:       http://pricing.payjunction.com/woothemes
Requires at least: 3.0.1 
Tested up to:      3.4.2
Stable tag:        1.0
Version:           1.0
License: GPLv2 or later

== Description ==
The WooCommerce PayJunction gateway plugin allows you to accept online payments in your WordPress WooCommerce store. Download the Payjunction plugin and start accepting payments from all major brands with your PayJunction account: Visa, MasterCard, American Express, Discover and more. 

== Installation ==
Simply follow these steps to install:

1. Unzip the file.
2. Upload the "woocommerce-gateway-PayJunction" folder to your WordPress Plugins directory.
3. Login to your WordPress Admin, then go to Plugins and activate the "WooCommerce PayJunction Gateway" plugin
4. Within the WordPress Admin, go to WooCommerce >> Settings, then click on the Payment Gateways tab, then click on the PayJunction link.
5. Enter the proper information...

   
To use in "Test Mode":  
    a. Check the box for "PayJunction Test"<br>
    b. Use the following API Username = pj-ql-01<br>
    c. Use the following API Password = pj-ql-01p<br>

Use the following to place a test transaction:<br>
    Test Credit Card Info:<br>
    Test Credit Card: 4444333322221111<br>
    Test Expiration Date: 01/20<br>
    
Test AVS & CVV Info:    
    Test CCV: 999 (If your settings are checking for CCV)<br>
    Test Address: 8320 (If your settings are checking for Address)<br>
    Zip Code: 85284 (If your settings are checking for Zip Code)	<br>

Now to check your test transactions:<br>
    1. Go to http://www.payjunctionlabs.com<br>
    2. Click on "Merchant Login"<br>
    3. Login: payjunctiondemo<br>
    4. Password: demo123<br>
    5. Go to the "Batches/History" section<br>
    6. Click on "View Current Batch"<br>

To "Go Live" (follow the steps outlined below):<br>
    1. Uncheck the box for "PayJunction Test"<br>
    2. Check the box "Enable PayJunction"<br>
    3. Enter the API Username and Password that was provided by PayJunction.  <br>

How to Find/Reset the Quicklink (API) Login and Password<br>
    1. While logged in to your Payjunction account, Click on "Gateway" or "Gateway Overview",in the top menu of your PayJunction account.<br>
    2. Scroll down to "Option #2" - "Quicklink: Advanced API Connection" <br>
    3. The QuickLink login is listed under "API Login"<br>
    4. If you want to modify the login or password, click on the "On" link, under "Status"<br>
    5. Scroll down to "API - Gateway", update your login and/or password<br>
    6. Click Save<br>

== Upgrade Notice ==
Comming soon
== Screenshots ==
Coming Soon 
== Changelog ==

2012.02.19 - version 1.0
 * First Release

== Frequently Asked Questions ==
Coming Soon
== Donations ==
Coming soon
