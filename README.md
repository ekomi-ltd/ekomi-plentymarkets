# plugin-plentymarkets-official

eKomi Plugin for plentymarkets allows you to integrate your plentymarkets shop 4 easily with eKomi system. This allows you to collect verified reviews, display eKomi seal on your website and get your seller ratings on Google. This helps you increase your website's click through rates, conversion rates and also, if you are running Google AdWord Campaigns, this helps in improving your Quality Score and hence your costs per click.

<p>
<strong>eKomi Reviews and Ratings allows you to:</strong>
</p>
<ul>
<li>Collect order and/or product base Reviews</li>
<li>Supports Simple, Configurable, Grouped and Bundle products</li>
<li>Manage Reviews: our team of Customer Feedback Managers, reviews each and every review for any terms which are not allowed and also put all negative reviews in moderation.</li>
<li>Publish reviews on search engines: Google, Bing, Yahoo!</li>
<li>Easy Integration with eKomi.</li>
<li>Get Google Seller Ratings.</li>
<li>Increase Click through Rate by over 17%</li>
<li>Increase conversion Rate</li>
</ul>

<p>eKomi is available in English, French, German, Spanish, Dutch, Italian, Russian and Polish<br />If you have any questions regarding the plugin, please contact your eKomi Account Manager.</p>

<p><b>Please note</b> that you will need an eKomi account to use the plugin. To create an eKomi account, go to 
<a href='http://eKomi.com'>eKomi.com</a>

## System Requirements

- plentymarkets version 7.0.0

## Known issues
- Not any known issue  

## Guides
1. [User Guide](https://ekomi01.atlassian.net/wiki/display/PD/Plentymarkets+-+Official+eKomi+Plugins)

### Installation

Follow these steps to install the plugin.

1. Login to Admin Panel
 
2. Go Start » Plugins

3. Add New Plugin
 
4. Add through Git
 
5. Enter Plugin Git URL & Git Account Credentials

    Remote Url: 
    ```
    https://github.com/ekomi-ltd/ekomi-plentymarkets.git
    ```
    User name: --your git username

    Password:  --your git password

    After inserting the details click on Test Connection button. It will validate the details.

    Branch: master

    And then Click on Save button.
 
6. Fetch The Latest Plugin Changes

 
7. Deploy EkomiIntegration Plugin In Productive It will take few minutes and then productive icon will turn to blue.
 
8. Select Clients
    - Click on Search icon
    - Choose Client(s)

9. Plugin Configuration

* Go to EkomiIntegration » Configuration
 
  - Enable / Disable The Plugin
  - Enable / Disable Product Reviews ( if enabled, product attributes will also be sent to eKomi i.e.  product id, name, image and URL )
  - Select Mode. (for SMS, mobile number format should be according E164)
  - Insert your Interface Shop Id
  - Insert your Interface Shop Secret
  - Choose Order Statuses on which you want to send information to eKomi.

  **Note:** Please make sure, The Shop Id and Secret is correct. In the case of invalid credentials the plugin will not work.
 
10. Set Enabled to “Yes” and save configuration
 

## Built With

* plentymarkets shop 4 framework

## Versioning

### v1.0.0 (16-04-2017)

- A complete working plugin

### v1.0.1 (03-07-2017)

- Exception handling error fixed

## Authors

* **eKomi** - [github profile](https://github.com/ekomi-ltd)

See also the list of [contributors](https://github.com/ekomi-ltd/plugin-plentymarkets-integration/graphs/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
