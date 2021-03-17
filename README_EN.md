## Cargus WooCommerce module installation manual

### Subscribe to API

- Access https://urgentcargus.portal.azure-api.net/
- Click the 'Sign up' button and fill in the form (you can not use the credentials that the client has for WebExpress)
- Confirm your registration by clicking on the link you received by mail (a real email address should be used)
- On the https://urgentcargus.portal.azure-api.net/developer page, click on `PRODUCTS` in the menu, then`
   UrgentOnlineAPI` and click 'Subscribe', then 'Confirm'
- After the Cargus team confirms subscription to the API, the customer receives a confirmation email
- On the https://urgentcargus.portal.azure-api.net/developer page, click on the user name at the top right, then click
   `Profile '
- The two subscription keys are masked by the characters `xxx ... xxx` and 'Show` in the right of each for display
- It is recommended to use `Primary key` in the Cargus module

### Installing the Module
   It installs like a regular wordpress module, uploads the folder 'urgentcargus' in '/ wp-content / plugins /' and installs it from the backend -> Modules.It is configured from the backend -> WooCommerce -> Settings -> Cargus delivery.
   After entering the address of the api, the key, the user and the password will appear in the same fields as in the other modules (lifting point, insurance, etc ...).

The module automatically creates AWBs when the commands are passed to one of the 'Processing' or 'Finished' states.
Similarly, the AWBs are deleted when the orders are switched to one of the 'Pending', 'Canceled', 'Refunded' or 'Failed' states.
