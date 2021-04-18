<h1>INSTALLATION<h1>

<h3>Install via Magento 1 admin</h3>
<b>1. Login to your Magento Admin panel and go to Magento Connect Manager.</b><br>
After logging in, go to System » Magento Connect » Magento Connect Manager. Enter your login credentials again if requested. 
<br><br>
<b>2. Paste CoinPayments extension URL and install.</b><br>
In Magento Connect Manager, find the “Paste extension key to install” field, and copy paste the following URL: 
<br>
Then, click “Install” followed by “Proceed” and wait for the installation process to complete. Afterwards, click “Return to Admin”.
<br><br>
<b>3. Locate CoinPayments extension in Payment Methods.</b><br>
Go to System » Configuration, find the SALES sub-menu and then click on Payment Methods – from there, scroll down and locate “Coinpayments.net”.
<br><br>
<b>4. Enable and configure the Coinpayments extension for Magento</b><br>
<ul>
    <li>To enable the CoinPayments extension for Magento, set “Enabled” to “Yes”.</li>
    <li>“Title” will appear on your checkout page – edit them as you feel is appropriate.</li>
    <li>Enter your API credentials – Merchant ID, IPN secret... You can find this keys in your coinpayments account</li>
</ul>
If you have not created your API credentials yet, login to your Coinpayments account and create one.
<ul>
    <li>Then, set the Receive Currency parameter to the currency in which you wish to receive your payouts from Coinpayments.</li>
    <li>Finally, click “Save Config” at the top right corner of the window.</li>
</ul>
<br><br>
<b>5. Go to frontend and see how it works!</b>
Congratulations – you did it! Your Magento store now has Coinpayments as a payment option at checkout.
<br><br>
<h3>Install via FTP</h3>
<ol>
    <li>Download plugin <a target="_blank" href="https://github.com/CoinPaymentsNet/coinpayments-magento">here</a>;</li>
    <li>Extract archive and upload to root directory of your Magento store;</li>
    <li>Login to Admin panel;</li>
    <li>Go to <b>System » Configuration</b>;</li>
    <li>Click on Payment Methods in <b>SALES</b> block;</li>
    <li>In Payment Methods find and click on Coinpayments. Please note, clear Magento cache if payment method not appeared;</li>
    <li>Set Enabled to Yes;</li>
    <li>Enter your API Auth Token (to create your API credentials: login to your Coinpayments account and find it there;</li>
    <li>Click Save Config.</li>
</ol>
