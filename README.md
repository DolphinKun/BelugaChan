# BelugaChan   
BelugaChan is an ultra-fast chan engine coded in PHP to replace vichan/LynxChan. 

### Features:
 - Board passwords
 - 100% salted IPs for everyone, including admins!
 - Captcha wall; blocks archive.is
 - Redis caching supported
 - Doesn't need JavaScript for everything
 - WebSockets for JavaScript users
 - Shadow banning support
### Supported databases
BelugaChan supports MySQL and SQLite. 
### Installation
Installation is as simple as installing the required PHP extensions, cloning this repo, logging in and changing the default password.
BelugaChan needs ``php-redis (for redis if enabled), php-gd, php-zmq (for websockets if enabled) php-imagick (for image thumbnails if enabled)``. PHP 7.3+ is required.

Once you have installed BelugaChan, the default login is: "admin/admin". The first word before the / is the username; next one is password!
While it is not required to enable captcha, it is recommended to stop spam/attacks.
### User roles
There are 5 user roles in BelugaChan:
 - Administrator (can also edit the config file using the web editor; be careful with this role)
 - Global Volunteer (can ban users, un-ban users, etc)
 - Board volunteer (limited to their granted boards that board owners have granted them to)
 - Board owner (only assignable by granting boards) 
 - User
### Additional notes
If you plan to use country flags, then you will need to obtain the GeoIP-2 country database from <https://www.maxmind.com/en/geoip2-country-database>; it cannot be packaged with BelugaChan by default due to the license agreement.

If you want PPH/the webring (I do not recommend this, but that's your own choice) feature, you will need to setup an hourly cron job for cron.php. You can put the below in your crontab config (adjust it as needed; for shared hosts, you can get rid of the cd command usually):  
`0 * * * * cd /mywebroot/ && php cron.php`
#### Help
I'm really stuck with the instructions; how to get help? Sure, post on <https://dolphinch.xyz/beluga/>.   
Will you scrape my data, dolphin? No, you host this on your servers, so you're safe; review the code if you don't 100% trust me.  
![DolphinChan](https://dolphinch.xyz/dolphin.jpg)