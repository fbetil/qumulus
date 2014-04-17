### Prerequisites

Webserver running PHP, MySQL.
HTTPS access is hightly recommended.

### Installation

Download latest version on GitHub : 

Extract ZIP content in a "qumulus" subfolder of your webserver.

Create a "qumulus" database and a "qumulus" user with all privileges on it.

### Configuration

Edit "<qumulus>/application/config-sample.php" and change theses values:

    $config['language']
    $config['encryption_key']
    $config['base_url']
    
Save file as "<qumulus>/application/config.php".
    
Edit "<qumulus>/application/database-sample.php" and change theses values:

$db['default']['hostname']
$db['default']['username']
$db['default']['password']
$db['default']['database']
$db['default']['pconnect'] //Optionnal if you have database disconnections

Save file as "<qumulus>/application/database.php".

### First run

Open your webbrowser and navigate to "https://myserver/qumulus".

That's all...

Enjoy !

