<html>
    <head>
        <style>
            .sixty-center {
                width:60%;
                margin:0 auto;
            }
            .doc-index a {
                display:block;
            }
            .doc-section img {
                width:100%;
                height:auto;
            }
            .doc-section {
                margin:60px 0;
                border:1px solid #e1e1e1;
                border-radius:5px;
                box-shadow:1px 2px 1px rgba(0,0,0,.4);
                padding:2%;
            }
            .txt-center {
                text-align:center;
            }
        </style>
    </head>
    <body>
        <div class="sixty-center">
            <h1 class="txt-center">Resources & Documentation</h1>
            <h2 class="txt-center"> Customer Facing Display</h2>
            <div class="doc-index" style="display:block">
                <h3>INDEX</h3>
                <a href="#setup">Setup</a>
                <a href="#install">- Install</a>
                <a href="#authenticate">- Authenticate</a>
                <a href="#register">- Register</a>
                <a href="#configure-settings">- Configure Settings</a>
                <a href="#setup-display">- Setup Display</a>
                <a href="#setup-controller">- Setup Controller</a>
                <a href="#display-screen">Display Screen</a>
                <a href="#ds-order-identifiers">- Order Identifiers</a>
                <a href="#ds-ready-orders">- - Ready Orders</a>
                <a href="#ds-preparing-orders">- - Preparing Orders</a>
                <a href="#ds-branding">- Branding</a>
                <a href="#ds-wait-time">- Wait Time</a>
                <a href="#controller-screen">Controller Screen</a>
                <a href="#cs-order-identifiers">- Order Identifiers</a>
                <a href="#cs-ready-orders">- - Ready Orders</a>
                <a href="#cs-preparing-orders">- - Preparing Orders</a>
                <a href="#settings-screen">Settings Screen</a>
                <a href="#ss-branding">- Branding</a>
                <a href="#ss-wait-time">- Wait Time</a>
                <a href="#ss-twilio-integration">-Twilio Integration</a>
            </div>
            <div class="doc-section">
                <h1 id="setup">Setup</h1>
                <h2 id="install">Install</h2>
                <p>
                    Install 'Customer Facing Display' on your Clover POS by:
                </p>
                <p>
                    In POS go to More Tools
                </p>
                <p>
                    Search for 'Customer Facing Display'
                </p>
                <p>
                    Click 'Customer Facing Display' Icon
                </p>
                <p>
                    Click 'Connect'
                </p>
                <p>
                    Read and Accept our Privacy Policy, and the Terms & Conditions
                </p>

                <h2 id="authenticate">Authenticate</h2>
                <p>
                    Open 'Customer Facing Display' app
                </p>
                <p>
                    On initial register screen Click 'Make Clover Authorization' Button 
                </p>
                <img src="/wp-content/uploads/2020/09/screencapture-veganmob-biz-cfd-register-php-2020-09-03-14_57_36-e1599709658211.png">
                <p>
                    Sign into Clover (if not already signed in)
                </p>
                <img src="/wp-content/uploads/2020/09/screencapture-sandbox-dev-clover-dashboard-login-2020-09-03-14_58_43-e1599709764195.png">
                <p>
                    Select your Merchant Location to Authorize permissions
                </p>
                <img src="/wp-content/uploads/2020/09/screencapture-sandbox-dev-clover-oauth-authorize-2020-09-03-14_59_31-e1599709560476.png">
                <h2 id="register">Register</h2>
                <p>
                    Input desired Name, Email, and Password 
                </p>
                <img src="/wp-content/uploads/2020/09/screencapture-veganmob-biz-cfd-sandbox-register-php-2020-09-03-14_59_59-e1599709603883.png">
                <p>
                    Click 'Submit'
                </p>
                <p>
                    Upon successful registration you see home screen display 'Authenticated'
                </p>
                <img src="/wp-content/uploads/2020/09/screencapture-veganmob-biz-cfd-sandbox-home-php-2020-09-03-15_01_03-e1599709633473.png">
                <h2 id="configure-settings">Configure Settings</h2>
                <p>
                    Go to ‘Settings’ and configure branding by uploading logo, changing font and highlighting color. Setup options for default wait time. Also configure Twilio if you'd like to enable the ability to send SMS text to customers.
                </p>
                <h2 id="setup-display">Setup Display</h2>
                <p>
                    On your display screen (Smart TV, Laptop, iPad) navigate to your browser and go to to https://veganmob.biz/cfd/display.php and login if necessary. Save to favorites. <a href="#display-screen">See more</a>.
                </p>
                <h2 id="setup-controller">Setup Controller</h2>
                <p>
                    On your seperate controller device (Laptop, iPad, etc.) navigate to your browser and go to to https://veganmob.biz/cfd/controller.php and login if necessary. <a href="#controller-screen">See more</a>.
                </p>
                <h2>
                    That’s it!
                </h2>
            </div>
            <div class="doc-section">
                <h1 id="display-screen">Display Screen</h1>
                <img src="/wp-content/uploads/2020/09/screencapture-veganmob-biz-cfd-sandbox-display-php-2020-09-03-15_02_45.png">
                <h2 id="ds-order-identifiers">Order Identifiers</h2>
                <p>
                    First Name and Last Initial displayed if customer name available, if no customer name in system it will display order number.
                </p>
                <h3 id="ds-ready-orders">Ready Orders </h3>
                <p>
                    Displayed at the top of the screen highlighted in branding colors.
                </p>
                <h3 id="ds-preparing-orders">Preparing orders </h3>
                <p>
                    Displayed underneath ready orders (if any), with a white background.
                </p>
                <p>
                    Make sure to disable eco settings and screen dimming in order to keep active. Consult your TV manual for more information.
                </p>
                <h2 id="ds-branding">Branding</h2>
                <p>
                    Display logo on left hand side, center. Upload a PNG image for best results.
                </p>
                <p>
                    Display Highlighted colors (change in settings) in the background of left hand side. Highlighted colors also displayed in background of ready orders.
                </p>
                <h2 id="ds-wait-time">Wait Time</h2>
                <p>
                    If orders were placed today and marked ready today the dynamic wait time will display on left. Default Wait time displayed on left side if there is no current data on wait time. 
                </p>
            </div>
            <div class="doc-section">
                <h1 id="controller-screen">Controller Screen</h1>
                <img src="/wp-content/uploads/2020/09/screencapture-veganmob-biz-cfd-sandbox-controller-php-2020-09-03-15_03_14-e1599710036388.png">
                <h2 id="cs-order-identifiers">Order Identifiers</h2>
                <p>
                    Full Name displayed if customer name available, if no customer name in system order number will be displayed. Name/order Number on left. Action Buttons on right.
                </p>
                <h3 id="cs-ready-orders">Ready Orders</h3>
                <p>
                    Displayed at the top highlighted with branding colors. 
                </p>
                <p>
                    Action buttons:
                </p>
                <p>
                    Text - send text messages to customers with phone number (if twilio integrates) (clients who gave phone is past referenced by phone number). 
                </p>
                <p>
                    Done - mark order as picked up, and remove from list.
                </p>
                <h3 id="cs-preparing-orders">Preparing orders </h3>
                <p>
                    Most recent orders on top. 
                </p>
                <p>
                    Action buttons: 
                </p>
                <p>
                    Ready - mark order as ready for pickup. Moves order to top and highlights it on both controller and display screen. 
                </p>
            </div>
            <div class="doc-section"> 
                <h1 id="settings-screen">Settings Screen</h1>
                <img src="/wp-content/uploads/2020/09/screencapture-veganmob-biz-cfd-sandbox-update-settings-php-2020-09-03-15_04_13.png">
                <h2 id="ss-branding">Branding</h2>
                <p>
                    Ability to upload logo and background image.
                </p>
                <p>
                    Font colors. Used in Display and Controller screens. Hex code and css colors allowed. (ex. #000000, or black)
                </p>
                <p>
                    Background/Highlight Colors: Used in Display and Controller Screens. Hex code, css colors, gradients allowed (ex. #ffffff, white, linear-gradient(90deg, rgba(255,255,255,1) 0%, rgba(0,0,0,1) 100%))
                </p>
                <p>
                    Set Display Title: (Title shown underneath logo on display screen)
                </p>
                <h2 id="ss-wait-time">Wait Time</h2>
                <p>
                    Set Default Wait Time. (Wait time shown on screen)
                </p>
                
                <h2 id="ss-twilio-integration">Twilio Integration</h2>
                <p>
                    Sign up for Twilio
                </p>
                <p>
                    Enter your SID, Secret, and Twilio Phone Number
                </p>
                <p>
                    Default Text Message: "[Customer Name], your order is ready at the pickup window. Please give us your first and last name when you arrive."
                </p>
            </div>
        </div>




    </body>
</html>