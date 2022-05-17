# WordPress Plugin: online-users-highlight
Free plugin to get all user online in your website

## How To use
You can add in your page as a shortcode and display a table of users or you can 
call the function to use the data whatever you want!
##### Shortcode
---
```php
echo do_shortcode("[online-users-highlight]");

Result:
```
![Result Shortcode](screenshot.png?raw=true)

##### Function
---
```php
foreach (get_online_users() as $user) {
    echo nl2br("ID: $user->ID  name: $user->display_name \n");
}

Result:

ID: 1 name: root
ID: 2 name: Jose Predo
```
