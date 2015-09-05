Money Manager EX - WebApp
====================

<p>Money Manager Ex WebApp allow you to insert new transaction directly from every device: It only needs a browser with HTML5 and internet connection to your webserver.<br />
Its claim is to add only a &quot;transaction remember&quot; with only some essential data, that will be reviewed calmly in desktop version.<br />
All transaction will be in fact downloaded at first startup of MoneyManagerEx desktop version, opening and reviewing transactions one by one. At the same time desktop version will update account, payees and categories in WebApp to keep them in sync between them.</p>
<p>
<h4>Requirements server side</h4>

<ul>
	<li>Webserver with PHP &gt; 5.4 (tested on Apache and IIS)</li>
	<li>PDO_SQLite extension (enabled by default on PHP &gt; 5.2)</li>
	<li>Full rights on the WebApp subfolder</li>
</ul>

<h4>Requirements client side</h4>

<ul>
	<li>Browser fully compatible with HTML5</li>
	<li>Optimized for portrait view on mobile device</li>
</ul>
</p>
<p>
<strong>RECOMMENDED ADVICE</strong><br>
We suggest using WebApp on a shared hosting service to greatly simplify installation process: most of free services meet the small requirements of WebApp.
If you know what you are doing, youcan install a webserver on your pc: after that you also need to properly configure your router to forward webserver port and reachit with a static IP or DNS, but for these you can find lots of guide so we will not discuss it here.
</p>
<p>
<h3>How to install WebApp</h3>

<ul>
	<li>Unzip last version in a folder on your webserver or upload files through FTP</li>
	<li>Rename htaccess.txt in .htaccess (on Windows you need to do it from CMD and &quot;rename&quot; command)</li>
	<li>Enable PDO_SQLite if disabled</li>
    <li>Enable (Apache) mod_rewrite</li>
</ul>
</p>
<p>
Now simply open your browser to the folder URL and insert first settings.<br />
GUID for data sync with desktop version is auto-generated, we suggest to don&#39;t change it
</p>
<p>
<br>
<h4>Upgrade</h4>
</p>
<p>
To upgrade first delete all file from webserver except:
<ul>
    <li>configuration_user.php</li>
    <li>MMEX_New_Transaction.db</li>
</ul>
</p>
<p>
Then unzip all files in the same folder as installation
First time you open WebApp, database file will be automatically upgraded to new version
</p>
