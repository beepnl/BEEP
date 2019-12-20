---
title: API Reference

language_tabs:
- bash
- javascript

includes:

search: true

toc_footers:
- <a href='http://github.com/mpociot/documentarian'>Documentation Powered by Documentarian</a>
---
<!-- START_INFO -->
# Info

Welcome to the generated API reference.
[Get Postman Collection](http://test.beep.nl/docs/collection.json)

<!-- END_INFO -->

#general


<!-- START_b3690d7048f832bb2ae7059b2ccd2d2e -->
## api/sensors
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/sensors" 
```

```javascript
const url = new URL("https://test.beep.nl/api/sensors");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/sensors`


<!-- END_b3690d7048f832bb2ae7059b2ccd2d2e -->

<!-- START_493153e007f201a68c09b94906fd38fd -->
## api/lora_sensors
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/lora_sensors" 
```

```javascript
const url = new URL("https://test.beep.nl/api/lora_sensors");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/lora_sensors`


<!-- END_493153e007f201a68c09b94906fd38fd -->

<!-- START_bcb7a14ed838926257563c68667a27c1 -->
## api/unsecure_sensors
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/unsecure_sensors" 
```

```javascript
const url = new URL("https://test.beep.nl/api/unsecure_sensors");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/unsecure_sensors`


<!-- END_bcb7a14ed838926257563c68667a27c1 -->

<!-- START_d7b7952e7fdddc07c978c9bdaf757acf -->
## api/register
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/register" 
```

```javascript
const url = new URL("https://test.beep.nl/api/register");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/register`


<!-- END_d7b7952e7fdddc07c978c9bdaf757acf -->

<!-- START_c3fa189a6c95ca36ad6ac4791a873d23 -->
## api/login
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/login" 
```

```javascript
const url = new URL("https://test.beep.nl/api/login");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/login`


<!-- END_c3fa189a6c95ca36ad6ac4791a873d23 -->

<!-- START_ecb0fb5f68c755d234f5903658956ead -->
## api/user/reminder
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/user/reminder" 
```

```javascript
const url = new URL("https://test.beep.nl/api/user/reminder");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/user/reminder`


<!-- END_ecb0fb5f68c755d234f5903658956ead -->

<!-- START_5a5b59444cee7eb79d151113de4eec9c -->
## api/user/reset
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/user/reset" 
```

```javascript
const url = new URL("https://test.beep.nl/api/user/reset");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/user/reset`


<!-- END_5a5b59444cee7eb79d151113de4eec9c -->

<!-- START_2d698b6d6bc7441f9c1a9cf11aec4059 -->
## Show the email verification notice.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/email/verify" 
```

```javascript
const url = new URL("https://test.beep.nl/api/email/verify");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response:

```json
null
```

### HTTP Request
`GET api/email/verify`


<!-- END_2d698b6d6bc7441f9c1a9cf11aec4059 -->

<!-- START_d83e982c7c8172810ed08568400567aa -->
## Mark the authenticated user&#039;s email address as verified.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/email/verify/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/email/verify/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (403):

```json
{
    "message": "Invalid signature."
}
```

### HTTP Request
`GET api/email/verify/{id}`


<!-- END_d83e982c7c8172810ed08568400567aa -->

<!-- START_007d2c80092c02b58e6bfecd510a3282 -->
## Resend the email verification notification.

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/email/resend" 
```

```javascript
const url = new URL("https://test.beep.nl/api/email/resend");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/email/resend`


<!-- END_007d2c80092c02b58e6bfecd510a3282 -->

<!-- START_d935edfdeccc953e11ef436a9d768e1c -->
## api/groups/checktoken
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/groups/checktoken" 
```

```javascript
const url = new URL("https://test.beep.nl/api/groups/checktoken");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/groups/checktoken`


<!-- END_d935edfdeccc953e11ef436a9d768e1c -->

<!-- START_4a6a89e9e0eaea9c72ceea57315f2c42 -->
## api/authenticate
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/authenticate" 
```

```javascript
const url = new URL("https://test.beep.nl/api/authenticate");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/authenticate`


<!-- END_4a6a89e9e0eaea9c72ceea57315f2c42 -->

<!-- START_298ee2ac22dee323822a5adff6d67b0a -->
## api/sensors
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/sensors" 
```

```javascript
const url = new URL("https://test.beep.nl/api/sensors");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/sensors`


<!-- END_298ee2ac22dee323822a5adff6d67b0a -->

<!-- START_8bf250dc088f0e2ec0e14604a09ce22c -->
## api/sensor
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/sensor" 
```

```javascript
const url = new URL("https://test.beep.nl/api/sensor");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/sensor`


<!-- END_8bf250dc088f0e2ec0e14604a09ce22c -->

<!-- START_a8632736dc0934be75ed89727073a6aa -->
## api/sensors/store
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/sensors/store" 
```

```javascript
const url = new URL("https://test.beep.nl/api/sensors/store");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/sensors/store`


<!-- END_a8632736dc0934be75ed89727073a6aa -->

<!-- START_f110a838c139b465d2aa0fe28eced864 -->
## api/sensors/measurements
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/sensors/measurements" 
```

```javascript
const url = new URL("https://test.beep.nl/api/sensors/measurements");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/sensors/measurements`


<!-- END_f110a838c139b465d2aa0fe28eced864 -->

<!-- START_5ed0763eff49928fbff019838d73ffce -->
## api/sensors/lastvalues
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/sensors/lastvalues" 
```

```javascript
const url = new URL("https://test.beep.nl/api/sensors/lastvalues");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/sensors/lastvalues`


<!-- END_5ed0763eff49928fbff019838d73ffce -->

<!-- START_cae771ed592df40719b12c9bcbf8409c -->
## api/sensors/lastweight
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/sensors/lastweight" 
```

```javascript
const url = new URL("https://test.beep.nl/api/sensors/lastweight");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/sensors/lastweight`


<!-- END_cae771ed592df40719b12c9bcbf8409c -->

<!-- START_6a462f7d1eb1f0e0377d2a3d717ab5f2 -->
## api/sensors/calibrateweight
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/sensors/calibrateweight" 
```

```javascript
const url = new URL("https://test.beep.nl/api/sensors/calibrateweight");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/sensors/calibrateweight`


<!-- END_6a462f7d1eb1f0e0377d2a3d717ab5f2 -->

<!-- START_458cef5981d281650d0312a814c62a17 -->
## api/sensors/offsetweight
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/sensors/offsetweight" 
```

```javascript
const url = new URL("https://test.beep.nl/api/sensors/offsetweight");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/sensors/offsetweight`


<!-- END_458cef5981d281650d0312a814c62a17 -->

<!-- START_1e1aaba3a713ac3ce04a89d5f4ad0f2e -->
## api/settings
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/settings" 
```

```javascript
const url = new URL("https://test.beep.nl/api/settings");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/settings`


<!-- END_1e1aaba3a713ac3ce04a89d5f4ad0f2e -->

<!-- START_10633908636252dc838d3a5bd392f07c -->
## api/settings
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/settings" 
```

```javascript
const url = new URL("https://test.beep.nl/api/settings");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/settings`


<!-- END_10633908636252dc838d3a5bd392f07c -->

<!-- START_f0dc9906634dd344e86ab96b3f489333 -->
## api/taxonomy/lists
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/taxonomy/lists" 
```

```javascript
const url = new URL("https://test.beep.nl/api/taxonomy/lists");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/taxonomy/lists`


<!-- END_f0dc9906634dd344e86ab96b3f489333 -->

<!-- START_39819739c1fe97abb680ee9f8137ec84 -->
## api/taxonomy/taxonomy
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/taxonomy/taxonomy" 
```

```javascript
const url = new URL("https://test.beep.nl/api/taxonomy/taxonomy");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/taxonomy/taxonomy`


<!-- END_39819739c1fe97abb680ee9f8137ec84 -->

<!-- START_ccf630a0f3579abb6ab3d47b9f4a65ab -->
## api/inspections/lists
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/inspections/lists" 
```

```javascript
const url = new URL("https://test.beep.nl/api/inspections/lists");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/inspections/lists`


<!-- END_ccf630a0f3579abb6ab3d47b9f4a65ab -->

<!-- START_8aaacf5f8f3d9bf8bff1980f0dbb99c5 -->
## api/inspections/{hive_id}
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/inspections/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/inspections/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/inspections/{hive_id}`


<!-- END_8aaacf5f8f3d9bf8bff1980f0dbb99c5 -->

<!-- START_aeffa3642b8d8eeca87b4c02f9b26262 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/inspections/hive/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/inspections/hive/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/inspections/hive/{hive_id}`


<!-- END_aeffa3642b8d8eeca87b4c02f9b26262 -->

<!-- START_eebc1891debfc7536a71b0f153ad21cd -->
## api/inspections/store
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/inspections/store" 
```

```javascript
const url = new URL("https://test.beep.nl/api/inspections/store");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/inspections/store`


<!-- END_eebc1891debfc7536a71b0f153ad21cd -->

<!-- START_78fa69f4fe2d3eed735b8d13151e5562 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/inspections/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/inspections/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/inspections/{id}`


<!-- END_78fa69f4fe2d3eed735b8d13151e5562 -->

<!-- START_5f81999d6a12d44ae368b63e6fae439d -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/research" 
```

```javascript
const url = new URL("https://test.beep.nl/api/research");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/research`


<!-- END_5f81999d6a12d44ae368b63e6fae439d -->

<!-- START_5ba9f23a9188b99ee63b3014619f551c -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/research/1/add_consent" 
```

```javascript
const url = new URL("https://test.beep.nl/api/research/1/add_consent");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/research/{id}/add_consent`


<!-- END_5ba9f23a9188b99ee63b3014619f551c -->

<!-- START_e41043cf612829839943f89dda227c03 -->
## api/research/{id}/remove_consent
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/research/1/remove_consent" 
```

```javascript
const url = new URL("https://test.beep.nl/api/research/1/remove_consent");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/research/{id}/remove_consent`


<!-- END_e41043cf612829839943f89dda227c03 -->

<!-- START_43e8ba205ffd3cbca826e9ab0a6f5af1 -->
## api/user
> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/user" 
```

```javascript
const url = new URL("https://test.beep.nl/api/user");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/user`


<!-- END_43e8ba205ffd3cbca826e9ab0a6f5af1 -->

<!-- START_e75f2f63a5a2351c4f4d83bc65cefcf8 -->
## api/user
> Example request:

```bash
curl -X PATCH "https://test.beep.nl/api/user" 
```

```javascript
const url = new URL("https://test.beep.nl/api/user");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PATCH",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PATCH api/user`


<!-- END_e75f2f63a5a2351c4f4d83bc65cefcf8 -->

<!-- START_a822c84c5aed22599b5c0f93e2ce41c1 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/checklists" 
```

```javascript
const url = new URL("https://test.beep.nl/api/checklists");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/checklists`


<!-- END_a822c84c5aed22599b5c0f93e2ce41c1 -->

<!-- START_3e3f29f45650477fd4a18d781913f05b -->
## api/checklists
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/checklists" 
```

```javascript
const url = new URL("https://test.beep.nl/api/checklists");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/checklists`


<!-- END_3e3f29f45650477fd4a18d781913f05b -->

<!-- START_c5e0350854444e0a45c0b055503dc0a4 -->
## api/checklists/{checklist}
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/checklists/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/checklists/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/checklists/{checklist}`


<!-- END_c5e0350854444e0a45c0b055503dc0a4 -->

<!-- START_794ee76fd055e386962fe5ec27954b9d -->
## api/checklists/{checklist}
> Example request:

```bash
curl -X PUT "https://test.beep.nl/api/checklists/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/checklists/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/checklists/{checklist}`

`PATCH api/checklists/{checklist}`


<!-- END_794ee76fd055e386962fe5ec27954b9d -->

<!-- START_4e47488f23358012f46746d57d9c46b0 -->
## api/checklists/{checklist}
> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/checklists/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/checklists/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/checklists/{checklist}`


<!-- END_4e47488f23358012f46746d57d9c46b0 -->

<!-- START_109013899e0bc43247b0f00b67f889cf -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/categories" 
```

```javascript
const url = new URL("https://test.beep.nl/api/categories");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/categories`


<!-- END_109013899e0bc43247b0f00b67f889cf -->

<!-- START_2335abbed7f782ea7d7dd6df9c738d74 -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/categories" 
```

```javascript
const url = new URL("https://test.beep.nl/api/categories");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/categories`


<!-- END_2335abbed7f782ea7d7dd6df9c738d74 -->

<!-- START_34925c1e31e7ecc53f8f52c8b1e91d44 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/categories/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/categories/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/categories/{category}`


<!-- END_34925c1e31e7ecc53f8f52c8b1e91d44 -->

<!-- START_549109b98c9f25ebff47fb4dc23423b6 -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/api/categories/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/categories/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/categories/{category}`

`PATCH api/categories/{category}`


<!-- END_549109b98c9f25ebff47fb4dc23423b6 -->

<!-- START_7513823f87b59040507bd5ab26f9ceb5 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/categories/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/categories/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/categories/{category}`


<!-- END_7513823f87b59040507bd5ab26f9ceb5 -->

<!-- START_007018a8a9f15c2d47fcb105431ffeee -->
## api/groups
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/groups" 
```

```javascript
const url = new URL("https://test.beep.nl/api/groups");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/groups`


<!-- END_007018a8a9f15c2d47fcb105431ffeee -->

<!-- START_15c22564ad248f952405021410fd1d25 -->
## api/groups
> Example request:

```bash
curl -X POST "https://test.beep.nl/api/groups" 
```

```javascript
const url = new URL("https://test.beep.nl/api/groups");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/groups`


<!-- END_15c22564ad248f952405021410fd1d25 -->

<!-- START_a209a43173c7c4aaf7ab070d77fb7f0c -->
## api/groups/{group}
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/groups/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/groups/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/groups/{group}`


<!-- END_a209a43173c7c4aaf7ab070d77fb7f0c -->

<!-- START_5b84408c838201930093112a7621935c -->
## api/groups/{group}
> Example request:

```bash
curl -X PUT "https://test.beep.nl/api/groups/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/groups/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/groups/{group}`

`PATCH api/groups/{group}`


<!-- END_5b84408c838201930093112a7621935c -->

<!-- START_bd4f731f3f84c755053406b8971eba1f -->
## api/groups/{group}
> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/groups/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/groups/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/groups/{group}`


<!-- END_bd4f731f3f84c755053406b8971eba1f -->

<!-- START_e5e611c362bfc5ad03913023307faa25 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/hives" 
```

```javascript
const url = new URL("https://test.beep.nl/api/hives");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/hives`


<!-- END_e5e611c362bfc5ad03913023307faa25 -->

<!-- START_80df36a766c9f45b8245df7a4c584eef -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/hives" 
```

```javascript
const url = new URL("https://test.beep.nl/api/hives");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/hives`


<!-- END_80df36a766c9f45b8245df7a4c584eef -->

<!-- START_6ec9bb72691ffb3b668ce33a42d1f9a3 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/hives/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/hives/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/hives/{hive}`


<!-- END_6ec9bb72691ffb3b668ce33a42d1f9a3 -->

<!-- START_e849cd4cd54a66f21f8716c15cfba36e -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/api/hives/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/hives/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/hives/{hive}`

`PATCH api/hives/{hive}`


<!-- END_e849cd4cd54a66f21f8716c15cfba36e -->

<!-- START_378a9cd4d171a263c8864789602f8fd8 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/hives/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/hives/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/hives/{hive}`


<!-- END_378a9cd4d171a263c8864789602f8fd8 -->

<!-- START_7fb4739b1e26173b78c06ed910857f37 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/locations" 
```

```javascript
const url = new URL("https://test.beep.nl/api/locations");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/locations`


<!-- END_7fb4739b1e26173b78c06ed910857f37 -->

<!-- START_6ac6759cab929b9077bddc6d56416b5c -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/locations" 
```

```javascript
const url = new URL("https://test.beep.nl/api/locations");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/locations`


<!-- END_6ac6759cab929b9077bddc6d56416b5c -->

<!-- START_f71771a70af5f8dad2212b1b5a2258d5 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/locations/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/locations/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/locations/{location}`


<!-- END_f71771a70af5f8dad2212b1b5a2258d5 -->

<!-- START_ddb58ef8759801169efb409d19aa45da -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/api/locations/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/locations/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/locations/{location}`

`PATCH api/locations/{location}`


<!-- END_ddb58ef8759801169efb409d19aa45da -->

<!-- START_fa28b5e8dd2e79a38ee29df19a80f037 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/locations/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/locations/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/locations/{location}`


<!-- END_fa28b5e8dd2e79a38ee29df19a80f037 -->

<!-- START_3dd824ece488b084ec5fce9c8c42eb30 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/productions" 
```

```javascript
const url = new URL("https://test.beep.nl/api/productions");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/productions`


<!-- END_3dd824ece488b084ec5fce9c8c42eb30 -->

<!-- START_67731ff70fa546e138dc8c8551071814 -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/productions" 
```

```javascript
const url = new URL("https://test.beep.nl/api/productions");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/productions`


<!-- END_67731ff70fa546e138dc8c8551071814 -->

<!-- START_434cf4b94a43998d13abf7311b0946aa -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/productions/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/productions/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/productions/{production}`


<!-- END_434cf4b94a43998d13abf7311b0946aa -->

<!-- START_03405779f48975554a16a3c4fd5dc2d9 -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/api/productions/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/productions/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/productions/{production}`

`PATCH api/productions/{production}`


<!-- END_03405779f48975554a16a3c4fd5dc2d9 -->

<!-- START_8ac584ff49e084887e1540dae0dbd539 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/productions/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/productions/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/productions/{production}`


<!-- END_8ac584ff49e084887e1540dae0dbd539 -->

<!-- START_6617e742ea9a08d8b9eb9c7b254615de -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/queens" 
```

```javascript
const url = new URL("https://test.beep.nl/api/queens");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/queens`


<!-- END_6617e742ea9a08d8b9eb9c7b254615de -->

<!-- START_af48b21ffd9099e4fe417ced8d762f7a -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/api/queens" 
```

```javascript
const url = new URL("https://test.beep.nl/api/queens");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/queens`


<!-- END_af48b21ffd9099e4fe417ced8d762f7a -->

<!-- START_a92861a394debdbec3528ef3c26d8a22 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/queens/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/queens/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/queens/{queen}`


<!-- END_a92861a394debdbec3528ef3c26d8a22 -->

<!-- START_20c827dbbb9d7175c20551b24a9b21bc -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/api/queens/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/queens/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/queens/{queen}`

`PATCH api/queens/{queen}`


<!-- END_20c827dbbb9d7175c20551b24a9b21bc -->

<!-- START_54031759896fd0124d491e1daa0637da -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/queens/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/queens/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/queens/{queen}`


<!-- END_54031759896fd0124d491e1daa0637da -->

<!-- START_e8fabede7787762b78140f2bfd317d77 -->
## api/groups/detach/{id}
> Example request:

```bash
curl -X DELETE "https://test.beep.nl/api/groups/detach/1" 
```

```javascript
const url = new URL("https://test.beep.nl/api/groups/detach/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/groups/detach/{id}`


<!-- END_e8fabede7787762b78140f2bfd317d77 -->

<!-- START_48911fc3cde4ec2d92e30c1511b44372 -->
## api/export
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/api/export" 
```

```javascript
const url = new URL("https://test.beep.nl/api/export");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (429):

```json
{
    "message": "Too Many Attempts."
}
```

### HTTP Request
`GET api/export`


<!-- END_48911fc3cde4ec2d92e30c1511b44372 -->

<!-- START_66e08d3cc8222573018fed49e121e96d -->
## Show the application&#039;s login form.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/login" 
```

```javascript
const url = new URL("https://test.beep.nl/login");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
null
```

### HTTP Request
`GET login`


<!-- END_66e08d3cc8222573018fed49e121e96d -->

<!-- START_ba35aa39474cb98cfb31829e70eb8b74 -->
## Handle a login request to the application.

> Example request:

```bash
curl -X POST "https://test.beep.nl/login" 
```

```javascript
const url = new URL("https://test.beep.nl/login");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST login`


<!-- END_ba35aa39474cb98cfb31829e70eb8b74 -->

<!-- START_e65925f23b9bc6b93d9356895f29f80c -->
## Log the user out of the application.

> Example request:

```bash
curl -X POST "https://test.beep.nl/logout" 
```

```javascript
const url = new URL("https://test.beep.nl/logout");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST logout`


<!-- END_e65925f23b9bc6b93d9356895f29f80c -->

<!-- START_ff38dfb1bd1bb7e1aa24b4e1792a9768 -->
## Show the application registration form.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/register" 
```

```javascript
const url = new URL("https://test.beep.nl/register");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
null
```

### HTTP Request
`GET register`


<!-- END_ff38dfb1bd1bb7e1aa24b4e1792a9768 -->

<!-- START_d7aad7b5ac127700500280d511a3db01 -->
## Handle a registration request for the application.

> Example request:

```bash
curl -X POST "https://test.beep.nl/register" 
```

```javascript
const url = new URL("https://test.beep.nl/register");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST register`


<!-- END_d7aad7b5ac127700500280d511a3db01 -->

<!-- START_d72797bae6d0b1f3a341ebb1f8900441 -->
## Display the form to request a password reset link.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/password/reset" 
```

```javascript
const url = new URL("https://test.beep.nl/password/reset");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
null
```

### HTTP Request
`GET password/reset`


<!-- END_d72797bae6d0b1f3a341ebb1f8900441 -->

<!-- START_feb40f06a93c80d742181b6ffb6b734e -->
## Send a reset link to the given user.

> Example request:

```bash
curl -X POST "https://test.beep.nl/password/email" 
```

```javascript
const url = new URL("https://test.beep.nl/password/email");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST password/email`


<!-- END_feb40f06a93c80d742181b6ffb6b734e -->

<!-- START_e1605a6e5ceee9d1aeb7729216635fd7 -->
## Display the password reset view for the given token.

If no token is present, display the link request form.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/password/reset/1" 
```

```javascript
const url = new URL("https://test.beep.nl/password/reset/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
null
```

### HTTP Request
`GET password/reset/{token}`


<!-- END_e1605a6e5ceee9d1aeb7729216635fd7 -->

<!-- START_cafb407b7a846b31491f97719bb15aef -->
## Reset the given user&#039;s password.

> Example request:

```bash
curl -X POST "https://test.beep.nl/password/reset" 
```

```javascript
const url = new URL("https://test.beep.nl/password/reset");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST password/reset`


<!-- END_cafb407b7a846b31491f97719bb15aef -->

<!-- START_30059a09ef3f0284c40e4d06962ce08d -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/dashboard" 
```

```javascript
const url = new URL("https://test.beep.nl/dashboard");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET dashboard`


<!-- END_30059a09ef3f0284c40e4d06962ce08d -->

<!-- START_1c81e414e9d762151e0d80d835253439 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/checklists" 
```

```javascript
const url = new URL("https://test.beep.nl/checklists");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET checklists`


<!-- END_1c81e414e9d762151e0d80d835253439 -->

<!-- START_41eae1e5d32ba430eb4da6854ff56379 -->
## Show the form for creating a new resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/checklists/create" 
```

```javascript
const url = new URL("https://test.beep.nl/checklists/create");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET checklists/create`


<!-- END_41eae1e5d32ba430eb4da6854ff56379 -->

<!-- START_5e6822a61f2c1fd697f8c5d4ca2863c7 -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/checklists" 
```

```javascript
const url = new URL("https://test.beep.nl/checklists");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST checklists`


<!-- END_5e6822a61f2c1fd697f8c5d4ca2863c7 -->

<!-- START_a3fe39d2be38ebc6090d3d48dd160723 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/checklists/1" 
```

```javascript
const url = new URL("https://test.beep.nl/checklists/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET checklists/{checklist}`


<!-- END_a3fe39d2be38ebc6090d3d48dd160723 -->

<!-- START_d97f06386629c7bd60cea6bbed9f83ee -->
## Show the form for editing the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/checklists/1/edit" 
```

```javascript
const url = new URL("https://test.beep.nl/checklists/1/edit");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET checklists/{checklist}/edit`


<!-- END_d97f06386629c7bd60cea6bbed9f83ee -->

<!-- START_3dee05c8ae7a96e0a51adc53c72154b6 -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/checklists/1" 
```

```javascript
const url = new URL("https://test.beep.nl/checklists/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT checklists/{checklist}`

`PATCH checklists/{checklist}`


<!-- END_3dee05c8ae7a96e0a51adc53c72154b6 -->

<!-- START_46ec2a44dc50af0965c9ce2a72393cbc -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/checklists/1" 
```

```javascript
const url = new URL("https://test.beep.nl/checklists/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE checklists/{checklist}`


<!-- END_46ec2a44dc50af0965c9ce2a72393cbc -->

<!-- START_3c07a3b07267bc22cc7e4e537a022cf6 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/inspections" 
```

```javascript
const url = new URL("https://test.beep.nl/inspections");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET inspections`


<!-- END_3c07a3b07267bc22cc7e4e537a022cf6 -->

<!-- START_b3ed545f9120e11a6bde2e2045d2dc51 -->
## Show the form for creating a new resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/inspections/create" 
```

```javascript
const url = new URL("https://test.beep.nl/inspections/create");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET inspections/create`


<!-- END_b3ed545f9120e11a6bde2e2045d2dc51 -->

<!-- START_eb3533a8a81ee1af05b1bfa7f06bb637 -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/inspections" 
```

```javascript
const url = new URL("https://test.beep.nl/inspections");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST inspections`


<!-- END_eb3533a8a81ee1af05b1bfa7f06bb637 -->

<!-- START_110a66ad404cc37d801eff2ce6ce28ec -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/inspections/1" 
```

```javascript
const url = new URL("https://test.beep.nl/inspections/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET inspections/{inspection}`


<!-- END_110a66ad404cc37d801eff2ce6ce28ec -->

<!-- START_670373ed43e33b29ed82733aa0793989 -->
## Show the form for editing the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/inspections/1/edit" 
```

```javascript
const url = new URL("https://test.beep.nl/inspections/1/edit");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET inspections/{inspection}/edit`


<!-- END_670373ed43e33b29ed82733aa0793989 -->

<!-- START_8345b9e04a4c60ab26115e46bf3497be -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/inspections/1" 
```

```javascript
const url = new URL("https://test.beep.nl/inspections/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT inspections/{inspection}`

`PATCH inspections/{inspection}`


<!-- END_8345b9e04a4c60ab26115e46bf3497be -->

<!-- START_801afaea58a3d70bbaf5f35f5ec1a45e -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/inspections/1" 
```

```javascript
const url = new URL("https://test.beep.nl/inspections/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE inspections/{inspection}`


<!-- END_801afaea58a3d70bbaf5f35f5ec1a45e -->

<!-- START_89966bfb9ab533cc3249b91a9090d3dc -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/users" 
```

```javascript
const url = new URL("https://test.beep.nl/users");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET users`


<!-- END_89966bfb9ab533cc3249b91a9090d3dc -->

<!-- START_04094f136cb91c117bde084191e6859d -->
## Show the form for creating a new resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/users/create" 
```

```javascript
const url = new URL("https://test.beep.nl/users/create");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET users/create`


<!-- END_04094f136cb91c117bde084191e6859d -->

<!-- START_57a8a4ba671355511e22780b1b63690e -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/users" 
```

```javascript
const url = new URL("https://test.beep.nl/users");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST users`


<!-- END_57a8a4ba671355511e22780b1b63690e -->

<!-- START_5693ac2f2e21af3ebc471cd5a6244460 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/users/1" 
```

```javascript
const url = new URL("https://test.beep.nl/users/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET users/{user}`


<!-- END_5693ac2f2e21af3ebc471cd5a6244460 -->

<!-- START_9c6e6c2d3215b1ba7d13468e7cd95e62 -->
## Show the form for editing the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/users/1/edit" 
```

```javascript
const url = new URL("https://test.beep.nl/users/1/edit");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET users/{user}/edit`


<!-- END_9c6e6c2d3215b1ba7d13468e7cd95e62 -->

<!-- START_7fe085c671e1b3d51e86136538b1d63f -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/users/1" 
```

```javascript
const url = new URL("https://test.beep.nl/users/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT users/{user}`

`PATCH users/{user}`


<!-- END_7fe085c671e1b3d51e86136538b1d63f -->

<!-- START_a948aef61c80bf96137d023464fde21f -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/users/1" 
```

```javascript
const url = new URL("https://test.beep.nl/users/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE users/{user}`


<!-- END_a948aef61c80bf96137d023464fde21f -->

<!-- START_6e3a68104e6332202d7aae1a30757fa5 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/languages" 
```

```javascript
const url = new URL("https://test.beep.nl/languages");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET languages`


<!-- END_6e3a68104e6332202d7aae1a30757fa5 -->

<!-- START_d7fd6c25e1bb206dc555dd50f9a9dbd7 -->
## Show the form for creating a new resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/languages/create" 
```

```javascript
const url = new URL("https://test.beep.nl/languages/create");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET languages/create`


<!-- END_d7fd6c25e1bb206dc555dd50f9a9dbd7 -->

<!-- START_718c3d830f1c1dc78c71e24db1947433 -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/languages/create" 
```

```javascript
const url = new URL("https://test.beep.nl/languages/create");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST languages/create`


<!-- END_718c3d830f1c1dc78c71e24db1947433 -->

<!-- START_1486492e9ded19d6ced98a4e2ff3f025 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/languages/1" 
```

```javascript
const url = new URL("https://test.beep.nl/languages/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET languages/{id}`


<!-- END_1486492e9ded19d6ced98a4e2ff3f025 -->

<!-- START_5d94fcc34f32af5e8aa588b14eb903e7 -->
## Show the form for editing the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/languages/1/edit" 
```

```javascript
const url = new URL("https://test.beep.nl/languages/1/edit");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET languages/{id}/edit`


<!-- END_5d94fcc34f32af5e8aa588b14eb903e7 -->

<!-- START_1fd10f8c480132fa3705263f497e3005 -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PATCH "https://test.beep.nl/languages/1" 
```

```javascript
const url = new URL("https://test.beep.nl/languages/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PATCH",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PATCH languages/{id}`


<!-- END_1fd10f8c480132fa3705263f497e3005 -->

<!-- START_a40c25de905aa0f6387a34ef5f5f4434 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/languages/1" 
```

```javascript
const url = new URL("https://test.beep.nl/languages/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE languages/{id}`


<!-- END_a40c25de905aa0f6387a34ef5f5f4434 -->

<!-- START_ea94c0913f19e66371e9ea92fd5ac136 -->
## translations
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/translations" 
```

```javascript
const url = new URL("https://test.beep.nl/translations");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET translations`


<!-- END_ea94c0913f19e66371e9ea92fd5ac136 -->

<!-- START_1d4b62e2c98907ee3892f50ac9045f93 -->
## translations/{language}
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/translations/1" 
```

```javascript
const url = new URL("https://test.beep.nl/translations/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET translations/{language}`


<!-- END_1d4b62e2c98907ee3892f50ac9045f93 -->

<!-- START_3dc89cabc4d0e0ec0b99926afbe3b5f5 -->
## translations/{language}
> Example request:

```bash
curl -X PATCH "https://test.beep.nl/translations/1" 
```

```javascript
const url = new URL("https://test.beep.nl/translations/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PATCH",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PATCH translations/{language}`


<!-- END_3dc89cabc4d0e0ec0b99926afbe3b5f5 -->

<!-- START_894dc227a1aa6e82ed701d71376e6119 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/groups" 
```

```javascript
const url = new URL("https://test.beep.nl/groups");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET groups`


<!-- END_894dc227a1aa6e82ed701d71376e6119 -->

<!-- START_ba2881c4d6a4e6f99de5937c8ed6da3e -->
## Show the form for creating a new resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/groups/create" 
```

```javascript
const url = new URL("https://test.beep.nl/groups/create");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET groups/create`


<!-- END_ba2881c4d6a4e6f99de5937c8ed6da3e -->

<!-- START_9d065d80067e14ef529651f1aa8ea697 -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/groups/create" 
```

```javascript
const url = new URL("https://test.beep.nl/groups/create");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST groups/create`


<!-- END_9d065d80067e14ef529651f1aa8ea697 -->

<!-- START_b4a4460070bc95c5dbc7cc5de80d7cf8 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/groups/1" 
```

```javascript
const url = new URL("https://test.beep.nl/groups/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET groups/{id}`


<!-- END_b4a4460070bc95c5dbc7cc5de80d7cf8 -->

<!-- START_fc3661edb98d0b7a40fbc653c165695f -->
## Show the form for editing the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/groups/1/edit" 
```

```javascript
const url = new URL("https://test.beep.nl/groups/1/edit");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET groups/{id}/edit`


<!-- END_fc3661edb98d0b7a40fbc653c165695f -->

<!-- START_cef946087a74af70d3c3881f7fbe9bde -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PATCH "https://test.beep.nl/groups/1" 
```

```javascript
const url = new URL("https://test.beep.nl/groups/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PATCH",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PATCH groups/{id}`


<!-- END_cef946087a74af70d3c3881f7fbe9bde -->

<!-- START_da1b51be7869c1c63107c99ef287e0d5 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/groups/1" 
```

```javascript
const url = new URL("https://test.beep.nl/groups/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE groups/{id}`


<!-- END_da1b51be7869c1c63107c99ef287e0d5 -->

<!-- START_47d8f23a2f737a390316d6c0917101a5 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/sensors" 
```

```javascript
const url = new URL("https://test.beep.nl/sensors");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET sensors`


<!-- END_47d8f23a2f737a390316d6c0917101a5 -->

<!-- START_5a7758b44cb7b491ce7704441925bdbd -->
## Show the form for creating a new resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/sensors/create" 
```

```javascript
const url = new URL("https://test.beep.nl/sensors/create");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET sensors/create`


<!-- END_5a7758b44cb7b491ce7704441925bdbd -->

<!-- START_becfe6e304d597d7dddbacdf582381ec -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/sensors/create" 
```

```javascript
const url = new URL("https://test.beep.nl/sensors/create");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST sensors/create`


<!-- END_becfe6e304d597d7dddbacdf582381ec -->

<!-- START_5ee123cb9588e3db1003c0ebcac1327f -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/sensors/1" 
```

```javascript
const url = new URL("https://test.beep.nl/sensors/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET sensors/{id}`


<!-- END_5ee123cb9588e3db1003c0ebcac1327f -->

<!-- START_45793faada1d8da1e590faa7f5b243f2 -->
## Show the form for editing the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/sensors/1/edit" 
```

```javascript
const url = new URL("https://test.beep.nl/sensors/1/edit");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET sensors/{id}/edit`


<!-- END_45793faada1d8da1e590faa7f5b243f2 -->

<!-- START_f6bd08423dbf04025ea8fd4c79c18d63 -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PATCH "https://test.beep.nl/sensors/1" 
```

```javascript
const url = new URL("https://test.beep.nl/sensors/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PATCH",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PATCH sensors/{id}`


<!-- END_f6bd08423dbf04025ea8fd4c79c18d63 -->

<!-- START_abc24e4e5099538ba8d53f10620e3066 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/sensors/1" 
```

```javascript
const url = new URL("https://test.beep.nl/sensors/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE sensors/{id}`


<!-- END_abc24e4e5099538ba8d53f10620e3066 -->

<!-- START_de5f4fc289db0f6abcc69bfdae1b0989 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/roles" 
```

```javascript
const url = new URL("https://test.beep.nl/roles");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET roles`


<!-- END_de5f4fc289db0f6abcc69bfdae1b0989 -->

<!-- START_ebd39f34dc5264d8b3f5f89531bf4193 -->
## Show the form for creating a new resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/roles/create" 
```

```javascript
const url = new URL("https://test.beep.nl/roles/create");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET roles/create`


<!-- END_ebd39f34dc5264d8b3f5f89531bf4193 -->

<!-- START_a7ec25c1f99380c426bd9f83c733514f -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/roles/create" 
```

```javascript
const url = new URL("https://test.beep.nl/roles/create");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST roles/create`


<!-- END_a7ec25c1f99380c426bd9f83c733514f -->

<!-- START_b257b533b697462127310b7b344bdab7 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/roles/1" 
```

```javascript
const url = new URL("https://test.beep.nl/roles/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET roles/{id}`


<!-- END_b257b533b697462127310b7b344bdab7 -->

<!-- START_f22024feacec640dc63a2ce40ec4b1aa -->
## Show the form for editing the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/roles/1/edit" 
```

```javascript
const url = new URL("https://test.beep.nl/roles/1/edit");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET roles/{id}/edit`


<!-- END_f22024feacec640dc63a2ce40ec4b1aa -->

<!-- START_8e66fc624453059a7ba2886e327513e0 -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PATCH "https://test.beep.nl/roles/1" 
```

```javascript
const url = new URL("https://test.beep.nl/roles/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PATCH",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PATCH roles/{id}`


<!-- END_8e66fc624453059a7ba2886e327513e0 -->

<!-- START_18cacfd4743194a549fe2b2b386648a4 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/roles/1" 
```

```javascript
const url = new URL("https://test.beep.nl/roles/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE roles/{id}`


<!-- END_18cacfd4743194a549fe2b2b386648a4 -->

<!-- START_4474f8fc2233dc1ed39e14a70b326972 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/permissions" 
```

```javascript
const url = new URL("https://test.beep.nl/permissions");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET permissions`


<!-- END_4474f8fc2233dc1ed39e14a70b326972 -->

<!-- START_83f315bc2983d7d8cf54d00d05fa078f -->
## Show the form for creating a new resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/permissions/create" 
```

```javascript
const url = new URL("https://test.beep.nl/permissions/create");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET permissions/create`


<!-- END_83f315bc2983d7d8cf54d00d05fa078f -->

<!-- START_0ff5b55b8896ca9dae04f57bf917646d -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/permissions" 
```

```javascript
const url = new URL("https://test.beep.nl/permissions");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST permissions`


<!-- END_0ff5b55b8896ca9dae04f57bf917646d -->

<!-- START_76b81f2dafe7c8380863ec7a30bf0d76 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/permissions/1" 
```

```javascript
const url = new URL("https://test.beep.nl/permissions/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET permissions/{permission}`


<!-- END_76b81f2dafe7c8380863ec7a30bf0d76 -->

<!-- START_cb16ad31dbf3e4c1c90ad446402dcb10 -->
## Show the form for editing the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/permissions/1/edit" 
```

```javascript
const url = new URL("https://test.beep.nl/permissions/1/edit");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET permissions/{permission}/edit`


<!-- END_cb16ad31dbf3e4c1c90ad446402dcb10 -->

<!-- START_9325b6759cf5b3f1d516140b53acf3ba -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/permissions/1" 
```

```javascript
const url = new URL("https://test.beep.nl/permissions/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT permissions/{permission}`

`PATCH permissions/{permission}`


<!-- END_9325b6759cf5b3f1d516140b53acf3ba -->

<!-- START_361d82c3a2a3a9bdcb8fe9a3a716b2f1 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/permissions/1" 
```

```javascript
const url = new URL("https://test.beep.nl/permissions/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE permissions/{permission}`


<!-- END_361d82c3a2a3a9bdcb8fe9a3a716b2f1 -->

<!-- START_a1baf7582816015871326cc199f2bb79 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/physicalquantity" 
```

```javascript
const url = new URL("https://test.beep.nl/physicalquantity");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET physicalquantity`


<!-- END_a1baf7582816015871326cc199f2bb79 -->

<!-- START_704b1e3c6d218e887e7e18fecc4514af -->
## Show the form for creating a new resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/physicalquantity/create" 
```

```javascript
const url = new URL("https://test.beep.nl/physicalquantity/create");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET physicalquantity/create`


<!-- END_704b1e3c6d218e887e7e18fecc4514af -->

<!-- START_3e4a204d2415912937ed00908bb72d86 -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/physicalquantity" 
```

```javascript
const url = new URL("https://test.beep.nl/physicalquantity");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST physicalquantity`


<!-- END_3e4a204d2415912937ed00908bb72d86 -->

<!-- START_b1d7a9ae554124dc82032877b687acd1 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/physicalquantity/1" 
```

```javascript
const url = new URL("https://test.beep.nl/physicalquantity/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET physicalquantity/{physicalquantity}`


<!-- END_b1d7a9ae554124dc82032877b687acd1 -->

<!-- START_04076d1297017bc0c776e19b201046e3 -->
## Show the form for editing the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/physicalquantity/1/edit" 
```

```javascript
const url = new URL("https://test.beep.nl/physicalquantity/1/edit");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET physicalquantity/{physicalquantity}/edit`


<!-- END_04076d1297017bc0c776e19b201046e3 -->

<!-- START_0e50821c1c678f5fc73ddbd4f0678a97 -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/physicalquantity/1" 
```

```javascript
const url = new URL("https://test.beep.nl/physicalquantity/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT physicalquantity/{physicalquantity}`

`PATCH physicalquantity/{physicalquantity}`


<!-- END_0e50821c1c678f5fc73ddbd4f0678a97 -->

<!-- START_8dfdb3a5e4c4fc48af3080070ae9562b -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/physicalquantity/1" 
```

```javascript
const url = new URL("https://test.beep.nl/physicalquantity/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE physicalquantity/{physicalquantity}`


<!-- END_8dfdb3a5e4c4fc48af3080070ae9562b -->

<!-- START_0d658b4098a0497f3487eb96b1f5131a -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/categoryinputs" 
```

```javascript
const url = new URL("https://test.beep.nl/categoryinputs");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET categoryinputs`


<!-- END_0d658b4098a0497f3487eb96b1f5131a -->

<!-- START_38ee5f3698c1ff1b2534319c4c509ccf -->
## Show the form for creating a new resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/categoryinputs/create" 
```

```javascript
const url = new URL("https://test.beep.nl/categoryinputs/create");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET categoryinputs/create`


<!-- END_38ee5f3698c1ff1b2534319c4c509ccf -->

<!-- START_97b79393737b9351ead24477bb197499 -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/categoryinputs" 
```

```javascript
const url = new URL("https://test.beep.nl/categoryinputs");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST categoryinputs`


<!-- END_97b79393737b9351ead24477bb197499 -->

<!-- START_81851c58ffe0ad07549e168261ba18d9 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/categoryinputs/1" 
```

```javascript
const url = new URL("https://test.beep.nl/categoryinputs/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET categoryinputs/{categoryinput}`


<!-- END_81851c58ffe0ad07549e168261ba18d9 -->

<!-- START_2db5f8b525f95d6d4b0d6f52c9a1a8e7 -->
## Show the form for editing the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/categoryinputs/1/edit" 
```

```javascript
const url = new URL("https://test.beep.nl/categoryinputs/1/edit");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET categoryinputs/{categoryinput}/edit`


<!-- END_2db5f8b525f95d6d4b0d6f52c9a1a8e7 -->

<!-- START_1c0d8e19ce7a4e339512a8ca0ade985d -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/categoryinputs/1" 
```

```javascript
const url = new URL("https://test.beep.nl/categoryinputs/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT categoryinputs/{categoryinput}`

`PATCH categoryinputs/{categoryinput}`


<!-- END_1c0d8e19ce7a4e339512a8ca0ade985d -->

<!-- START_e5e42f6765a6e8282531cd143c57d944 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/categoryinputs/1" 
```

```javascript
const url = new URL("https://test.beep.nl/categoryinputs/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE categoryinputs/{categoryinput}`


<!-- END_e5e42f6765a6e8282531cd143c57d944 -->

<!-- START_069c27eeb2b0a0dd3670407426510f61 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/inspection-items" 
```

```javascript
const url = new URL("https://test.beep.nl/inspection-items");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET inspection-items`


<!-- END_069c27eeb2b0a0dd3670407426510f61 -->

<!-- START_912601d1e2efa63aaf719d0639550a6e -->
## Show the form for creating a new resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/inspection-items/create" 
```

```javascript
const url = new URL("https://test.beep.nl/inspection-items/create");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET inspection-items/create`


<!-- END_912601d1e2efa63aaf719d0639550a6e -->

<!-- START_0793e404430f90adfe752af36a554976 -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/inspection-items" 
```

```javascript
const url = new URL("https://test.beep.nl/inspection-items");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST inspection-items`


<!-- END_0793e404430f90adfe752af36a554976 -->

<!-- START_019b2a6e58418ef5ebe89c98058c53ba -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/inspection-items/1" 
```

```javascript
const url = new URL("https://test.beep.nl/inspection-items/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET inspection-items/{inspection_item}`


<!-- END_019b2a6e58418ef5ebe89c98058c53ba -->

<!-- START_116bf308fa3e73c0c17bb6921c622c63 -->
## Show the form for editing the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/inspection-items/1/edit" 
```

```javascript
const url = new URL("https://test.beep.nl/inspection-items/1/edit");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET inspection-items/{inspection_item}/edit`


<!-- END_116bf308fa3e73c0c17bb6921c622c63 -->

<!-- START_604876bf8dd87c0ae1279cf29eaaf6a8 -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/inspection-items/1" 
```

```javascript
const url = new URL("https://test.beep.nl/inspection-items/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT inspection-items/{inspection_item}`

`PATCH inspection-items/{inspection_item}`


<!-- END_604876bf8dd87c0ae1279cf29eaaf6a8 -->

<!-- START_2019a09d1be9b1865f78165bb233a021 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/inspection-items/1" 
```

```javascript
const url = new URL("https://test.beep.nl/inspection-items/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE inspection-items/{inspection_item}`


<!-- END_2019a09d1be9b1865f78165bb233a021 -->

<!-- START_5a455e354524f1b0729a7ef6645a7ce1 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/measurement" 
```

```javascript
const url = new URL("https://test.beep.nl/measurement");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET measurement`


<!-- END_5a455e354524f1b0729a7ef6645a7ce1 -->

<!-- START_769d97cc5fb5d64515846331d8981f19 -->
## Show the form for creating a new resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/measurement/create" 
```

```javascript
const url = new URL("https://test.beep.nl/measurement/create");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET measurement/create`


<!-- END_769d97cc5fb5d64515846331d8981f19 -->

<!-- START_54926a51f674cad33989ccb31e648c89 -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/measurement" 
```

```javascript
const url = new URL("https://test.beep.nl/measurement");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST measurement`


<!-- END_54926a51f674cad33989ccb31e648c89 -->

<!-- START_b440a6c68a6b37ef83bbdd5ca69416e8 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/measurement/1" 
```

```javascript
const url = new URL("https://test.beep.nl/measurement/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET measurement/{measurement}`


<!-- END_b440a6c68a6b37ef83bbdd5ca69416e8 -->

<!-- START_6b05debe3cb9996c3ecff0e9420cfaef -->
## Show the form for editing the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/measurement/1/edit" 
```

```javascript
const url = new URL("https://test.beep.nl/measurement/1/edit");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET measurement/{measurement}/edit`


<!-- END_6b05debe3cb9996c3ecff0e9420cfaef -->

<!-- START_90a79b810c77bf2f4bfaa5ea1b3913d6 -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/measurement/1" 
```

```javascript
const url = new URL("https://test.beep.nl/measurement/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT measurement/{measurement}`

`PATCH measurement/{measurement}`


<!-- END_90a79b810c77bf2f4bfaa5ea1b3913d6 -->

<!-- START_cff48b78377cceef7d6ac907ebb37dfb -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/measurement/1" 
```

```javascript
const url = new URL("https://test.beep.nl/measurement/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE measurement/{measurement}`


<!-- END_cff48b78377cceef7d6ac907ebb37dfb -->

<!-- START_0505c379c344e5649906e64ed8cff33e -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/research" 
```

```javascript
const url = new URL("https://test.beep.nl/research");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET research`


<!-- END_0505c379c344e5649906e64ed8cff33e -->

<!-- START_173346a480514dd29024d7ea2209762e -->
## Show the form for creating a new resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/research/create" 
```

```javascript
const url = new URL("https://test.beep.nl/research/create");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET research/create`


<!-- END_173346a480514dd29024d7ea2209762e -->

<!-- START_8974c60790c373ced43dcbfdb1d8d4cb -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/research" 
```

```javascript
const url = new URL("https://test.beep.nl/research");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST research`


<!-- END_8974c60790c373ced43dcbfdb1d8d4cb -->

<!-- START_d051d92ae4d574979eaa08b0f367a7fc -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/research/1" 
```

```javascript
const url = new URL("https://test.beep.nl/research/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET research/{research}`


<!-- END_d051d92ae4d574979eaa08b0f367a7fc -->

<!-- START_ba46d5dcb2be2c1f2ac0704428dd6eb0 -->
## Show the form for editing the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/research/1/edit" 
```

```javascript
const url = new URL("https://test.beep.nl/research/1/edit");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET research/{research}/edit`


<!-- END_ba46d5dcb2be2c1f2ac0704428dd6eb0 -->

<!-- START_c70c233d78f12e59006788e6201f1dae -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/research/1" 
```

```javascript
const url = new URL("https://test.beep.nl/research/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT research/{research}`

`PATCH research/{research}`


<!-- END_c70c233d78f12e59006788e6201f1dae -->

<!-- START_f8fb63cfe685696acd524346d5741c93 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://test.beep.nl/research/1" 
```

```javascript
const url = new URL("https://test.beep.nl/research/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE research/{research}`


<!-- END_f8fb63cfe685696acd524346d5741c93 -->

<!-- START_6a107a131f853e92450ac742beba0e7f -->
## categories
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/categories" 
```

```javascript
const url = new URL("https://test.beep.nl/categories");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET categories`


<!-- END_6a107a131f853e92450ac742beba0e7f -->

<!-- START_6a2ad9b453d77d03400b055f92e9426f -->
## Show the form for creating a new resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/categories/create" 
```

```javascript
const url = new URL("https://test.beep.nl/categories/create");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET categories/create`


<!-- END_6a2ad9b453d77d03400b055f92e9426f -->

<!-- START_cb37a009c57f6e054e721aada4d9664b -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://test.beep.nl/categories" 
```

```javascript
const url = new URL("https://test.beep.nl/categories");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST categories`


<!-- END_cb37a009c57f6e054e721aada4d9664b -->

<!-- START_1038e1f50fce16240ff593d39167770f -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/categories/1" 
```

```javascript
const url = new URL("https://test.beep.nl/categories/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET categories/{category}`


<!-- END_1038e1f50fce16240ff593d39167770f -->

<!-- START_bd3c894d3ea5ccd46778dcf41ef0ff3c -->
## Show the form for editing the specified resource.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/categories/1/edit" 
```

```javascript
const url = new URL("https://test.beep.nl/categories/1/edit");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET categories/{category}/edit`


<!-- END_bd3c894d3ea5ccd46778dcf41ef0ff3c -->

<!-- START_5c7692955c3e2542b25146f0d77e3767 -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://test.beep.nl/categories/1" 
```

```javascript
const url = new URL("https://test.beep.nl/categories/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT categories/{category}`

`PATCH categories/{category}`


<!-- END_5c7692955c3e2542b25146f0d77e3767 -->

<!-- START_c595e22ac497b4ace0ad442feffe7712 -->
## categories/{category}
> Example request:

```bash
curl -X DELETE "https://test.beep.nl/categories/1" 
```

```javascript
const url = new URL("https://test.beep.nl/categories/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE categories/{category}`


<!-- END_c595e22ac497b4ace0ad442feffe7712 -->

<!-- START_5ebfee16a7ecccc9bd52c26319076a9b -->
## categories/{id}/pop
> Example request:

```bash
curl -X DELETE "https://test.beep.nl/categories/1/pop" 
```

```javascript
const url = new URL("https://test.beep.nl/categories/1/pop");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE categories/{id}/pop`


<!-- END_5ebfee16a7ecccc9bd52c26319076a9b -->

<!-- START_47d943289fe1d213c7371e8ae72aa031 -->
## categories/{id}/fix
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/categories/1/fix" 
```

```javascript
const url = new URL("https://test.beep.nl/categories/1/fix");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET categories/{id}/fix`


<!-- END_47d943289fe1d213c7371e8ae72aa031 -->

<!-- START_b6dd1bf91f3ab4a12a43a83d419a7873 -->
## taxonomy/display
> Example request:

```bash
curl -X GET -G "https://test.beep.nl/taxonomy/display" 
```

```javascript
const url = new URL("https://test.beep.nl/taxonomy/display");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET taxonomy/display`


<!-- END_b6dd1bf91f3ab4a12a43a83d419a7873 -->

<!-- START_8a433b8054446930ab5624e315f668c7 -->
## checklists/destroy/copies
> Example request:

```bash
curl -X DELETE "https://test.beep.nl/checklists/destroy/copies" 
```

```javascript
const url = new URL("https://test.beep.nl/checklists/destroy/copies");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE checklists/destroy/copies`


<!-- END_8a433b8054446930ab5624e315f668c7 -->

<!-- START_c88fc6aa6eb1bee7a494d3c0a02038b1 -->
## Show the email verification notice.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/email/verify" 
```

```javascript
const url = new URL("https://test.beep.nl/email/verify");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response:

```json
null
```

### HTTP Request
`GET email/verify`


<!-- END_c88fc6aa6eb1bee7a494d3c0a02038b1 -->

<!-- START_af069c1c23cec25f2be1688621969179 -->
## Mark the authenticated user&#039;s email address as verified.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/email/verify/1" 
```

```javascript
const url = new URL("https://test.beep.nl/email/verify/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (403):

```json
{
    "message": "Invalid signature."
}
```

### HTTP Request
`GET email/verify/{id}`


<!-- END_af069c1c23cec25f2be1688621969179 -->

<!-- START_b44c38c624a55f23870089f09fba5cef -->
## Resend the email verification notification.

> Example request:

```bash
curl -X GET -G "https://test.beep.nl/email/resend" 
```

```javascript
const url = new URL("https://test.beep.nl/email/resend");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (400):

```json
"invalid_user"
```

### HTTP Request
`GET email/resend`


<!-- END_b44c38c624a55f23870089f09fba5cef -->


