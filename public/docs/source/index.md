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
[Get Postman Collection](http://api.beep.nl/docs/collection.json)

<!-- END_INFO -->

#Api\AlertController


<!-- START_27e60fb6c87430f1ee280ffc44a9fb33 -->
## api/alerts GET
List all user alerts that are not deleted.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/alerts" 
```

```javascript
const url = new URL("https://api.beep.nl/api/alerts");

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
`GET api/alerts`


<!-- END_27e60fb6c87430f1ee280ffc44a9fb33 -->

<!-- START_ff0acf0994a378184df481412873c9b1 -->
## api/alerts/{id} POST
Create the specified user alert.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X POST "https://api.beep.nl/api/alerts" \
    -H "Content-Type: application/json" \
    -d '{"alert_rule_id":13,"measurement_id":11,"alert_value":"impedit","show":true}'

```

```javascript
const url = new URL("https://api.beep.nl/api/alerts");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "alert_rule_id": 13,
    "measurement_id": 11,
    "alert_value": "impedit",
    "show": true
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/alerts`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    alert_rule_id | integer |  required  | The alert rule that has been alerted for.
    measurement_id | integer |  required  | The physical quantity / unit to alert for.
    alert_value | string |  required  | The alert value.
    show | boolean |  optional  | Set to false (0) if the alert should NOT be shown anymore.

<!-- END_ff0acf0994a378184df481412873c9b1 -->

<!-- START_507f78ac919fb619f0c367bc95e11f60 -->
## api/alerts/{id} GET
Display the specified user alert.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/alerts/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/alerts/1");

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
`GET api/alerts/{alert}`


<!-- END_507f78ac919fb619f0c367bc95e11f60 -->

<!-- START_b56b064d2a87f2e7b972f94f154f9b65 -->
## api/alerts/{id} PATCH
Update the specified user alert.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X PUT "https://api.beep.nl/api/alerts/1" \
    -H "Content-Type: application/json" \
    -d '{"show":false}'

```

```javascript
const url = new URL("https://api.beep.nl/api/alerts/1");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "show": false
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/alerts/{alert}`

`PATCH api/alerts/{alert}`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    show | boolean |  optional  | Set to false (0) if the alert should NOT be shown anymore.

<!-- END_b56b064d2a87f2e7b972f94f154f9b65 -->

<!-- START_a1fc881bc679bc6f057324af55ec6e72 -->
## api/alerts/{id} DELETE
Delete the specified user alert, or all if id === &#039;all&#039;, or specific id&#039;s when provided &amp;alert_ids=1,4,7

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X DELETE "https://api.beep.nl/api/alerts/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/alerts/1");

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
`DELETE api/alerts/{alert}`


<!-- END_a1fc881bc679bc6f057324af55ec6e72 -->

#Api\AlertRuleController


<!-- START_adb377f80aad712e86ace99583e397b0 -->
## api/alert-rules GET
List all user alert rules that are not deleted.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/alert-rules" 
```

```javascript
const url = new URL("https://api.beep.nl/api/alert-rules");

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
`GET api/alert-rules`


<!-- END_adb377f80aad712e86ace99583e397b0 -->

<!-- START_9fab7b3346de0166be92e0e195edc7c4 -->
## api/alert-rules/{id} POST
Create the specified user alert rule.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X POST "https://api.beep.nl/api/alert-rules" \
    -H "Content-Type: application/json" \
    -d '{"measurement_id":20,"calculation":"voluptas","comparator":"ut","comparison":"quasi","threshold_value":20557785,"name":"sunt","description":"sunt","calculation_minutes":20,"exclude_months":"[1,2,3,11,12]","exclude_hours":"[0,1,2,3,22,23]","exclude_hive_ids":[],"alert_on_occurrences":20,"alert_via_email":true,"webhook_url":"modi","active":false}'

```

```javascript
const url = new URL("https://api.beep.nl/api/alert-rules");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "measurement_id": 20,
    "calculation": "voluptas",
    "comparator": "ut",
    "comparison": "quasi",
    "threshold_value": 20557785,
    "name": "sunt",
    "description": "sunt",
    "calculation_minutes": 20,
    "exclude_months": "[1,2,3,11,12]",
    "exclude_hours": "[0,1,2,3,22,23]",
    "exclude_hive_ids": [],
    "alert_on_occurrences": 20,
    "alert_via_email": true,
    "webhook_url": "modi",
    "active": false
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/alert-rules`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    measurement_id | integer |  required  | The physical quantity / unit to alert for.
    calculation | string |  required  | Calculation to be done with measurement value(s): (min, max, ave, der, cnt) -> Minimum, Maximum, Average (mean), Derivative, Count.
    comparator | string |  required  | Logical comparator to perform with comparison calculation result and threshold_value (=, <, >, <=, >=).
    comparison | string |  required  | Comparison function to perform with measurement value(s): (val, dif, abs, abs_dif) -> Value, Difference, Absolute value, Absolute value of the difference.
    threshold_value | float |  required  | The threshold value beyond which the alert will be sent.
    name | string |  optional  | The name of the alert rule.
    description | string |  optional  | The description of the alert rule.
    calculation_minutes | integer |  optional  | The amount of minutes used for calculating the (min, max, ave, der, cnt) of the measurement value(s). If not provided, the last recorded value is used as a reference.
    exclude_months | array |  optional  | Array of month indexes (1-12). If not filled the standard alert is 'always on'.
    exclude_hours | array |  optional  | Array of hour indexes (0-23). If not filled the standard alert is 'always on'.
    exclude_hive_ids | array |  optional  | Array of Hive ids. If not filled the standard alert is evaluated on 'all hives'.
    alert_on_occurrences | integer |  optional  | Amount of occurences that a calculated value goed beyond the threshold_value. If not filled the standard is 1 (immediate alert).
    alert_via_email | boolean |  optional  | Set to false (0) if an e-mail should NOT be sent on alert. Default: true (1).
    webhook_url | string |  optional  | URL of optional endpoint to call on alert for web hook integration.
    active | boolean |  optional  | Set to false (0) if the alert should NOT be active. Default: true (1).

<!-- END_9fab7b3346de0166be92e0e195edc7c4 -->

<!-- START_9227af5fa7385eb43511954236120933 -->
## api/alert-rules/{id} GET
Display the specified user alert rules.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/alert-rules/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/alert-rules/1");

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
`GET api/alert-rules/{alert_rule}`


<!-- END_9227af5fa7385eb43511954236120933 -->

<!-- START_3f4fbed2deb297471666935d99d5c636 -->
## api/alert-rules/{id} PATCH
Update the specified user alert rule.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X PUT "https://api.beep.nl/api/alert-rules/1" \
    -H "Content-Type: application/json" \
    -d '{"name":"enim","description":"aliquid","measurement_id":15,"calculation":"recusandae","comparator":"aspernatur","comparison":"quisquam","threshold_value":3888609,"calculation_minutes":3,"exclude_months":"[1,2,3,11,12]","exclude_hours":"[0,1,2,3,22,23]","exclude_hive_ids":[],"alert_on_occurrences":10,"alert_via_email":true,"webhook_url":"omnis","active":false}'

```

```javascript
const url = new URL("https://api.beep.nl/api/alert-rules/1");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "name": "enim",
    "description": "aliquid",
    "measurement_id": 15,
    "calculation": "recusandae",
    "comparator": "aspernatur",
    "comparison": "quisquam",
    "threshold_value": 3888609,
    "calculation_minutes": 3,
    "exclude_months": "[1,2,3,11,12]",
    "exclude_hours": "[0,1,2,3,22,23]",
    "exclude_hive_ids": [],
    "alert_on_occurrences": 10,
    "alert_via_email": true,
    "webhook_url": "omnis",
    "active": false
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/alert-rules/{alert_rule}`

`PATCH api/alert-rules/{alert_rule}`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    name | string |  optional  | The name of the alert rule.
    description | string |  optional  | The description of the alert rule.
    measurement_id | integer |  required  | The physical quantity / unit to alert for.
    calculation | string |  required  | Calculation to be done with measurement value(s): (min, max, ave, der, cnt) -> Minimum, Maximum, Average (mean), Derivative, Count.
    comparator | string |  required  | Logical comparator to perform with comparison calculation result and threshold_value (=, <, >, <=, >=).
    comparison | string |  required  | Comparison function to perform with measurement value(s): (val, dif, abs, abs_dif) -> Value, Difference, Absolute value, Absolute value of the difference.
    threshold_value | float |  required  | The threshold value beyond which the alert will be sent.
    calculation_minutes | integer |  optional  | The amount of minutes used for calculating the (min, max, ave, der, cnt) of the measurement value(s). If not provided, the last recorded value is used as a reference.
    exclude_months | array |  optional  | Array of month indexes (1-12). If not filled the standard alert is 'always on'.
    exclude_hours | array |  optional  | Array of hour indexes (0-23). If not filled the standard alert is 'always on'.
    exclude_hive_ids | array |  optional  | Array of Hive ids. If not filled the standard alert is evaluated on 'all hives'.
    alert_on_occurrences | integer |  optional  | Amount of occurences that a calculated value goed beyond the threshold_value. If not filled the standard is 1 (immediate alert).
    alert_via_email | boolean |  optional  | Set to false (0) if an e-mail should NOT be sent on alert. Default: true (1).
    webhook_url | string |  optional  | URL of optional endpoint to call on alert for web hook integration.
    active | boolean |  optional  | Set to false (0) if the alert should NOT be active. Default: true (1).

<!-- END_3f4fbed2deb297471666935d99d5c636 -->

<!-- START_ffb2ec030fc2118925c07e196067779c -->
## api/alert-rules/{id} DELETE
Delete the specified user alert rule.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X DELETE "https://api.beep.nl/api/alert-rules/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/alert-rules/1");

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
`DELETE api/alert-rules/{alert_rule}`


<!-- END_ffb2ec030fc2118925c07e196067779c -->

<!-- START_ddc4a64a5c81a0933dc450079fcd62ba -->
## api/alert-rules-default GET
List all default alert rules that are available.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/alert-rules-default" 
```

```javascript
const url = new URL("https://api.beep.nl/api/alert-rules-default");

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
`GET api/alert-rules-default`


<!-- END_ddc4a64a5c81a0933dc450079fcd62ba -->

#Api\Auth\VerificationController

User verification functions
<!-- START_d83e982c7c8172810ed08568400567aa -->
## Mark the authenticated user&#039;s email address as verified.

> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/email/verify/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/email/verify/1");

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
curl -X POST "https://api.beep.nl/api/email/resend" 
```

```javascript
const url = new URL("https://api.beep.nl/api/email/resend");

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

#Api\CategoryController

All categories in the categorization tree used for hive inspections
Only used to get listing (index) or one category (show)
<!-- START_109013899e0bc43247b0f00b67f889cf -->
## api/categories
Display a listing of the inspection categories.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/categories" 
```

```javascript
const url = new URL("https://api.beep.nl/api/categories");

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
[
    {
        "id": 1,
        "type": "system",
        "name": "apiary",
        "icon": null,
        "source": null,
        "required": 0,
        "input": "list",
        "trans": {
            "en": "Apiary",
            "nl": "Bijenstand",
            "de": "Bienenstand",
            "fr": "Rucher",
            "ro": "Stupină",
            "pt": "Apiário",
            "es": "Apiario",
            "da": "Bigård"
        },
        "unit": null,
        "children": [
            {
                "id": 2,
                "type": "0",
                "name": "name",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "text",
                "trans": {
                    "en": "Name",
                    "nl": "Naam",
                    "de": "Name",
                    "fr": "Nom",
                    "ro": "Nume",
                    "pt": "Nome",
                    "es": "Nombre",
                    "da": "Navn"
                },
                "unit": null
            },
            {
                "id": 3,
                "type": "list",
                "name": "location",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "select_location",
                "trans": {
                    "en": "Location",
                    "nl": "Locatie",
                    "de": "Ort",
                    "fr": "Lieux",
                    "ro": "Locație",
                    "pt": "Localização",
                    "es": "Ubicación",
                    "da": "Lokation"
                },
                "unit": null
            },
            {
                "id": 12,
                "type": "1",
                "name": "number_of_bee_colonies",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "number_positive",
                "trans": {
                    "en": "Number of bee colonies",
                    "nl": "Aantal bijenvolken",
                    "de": "Anzahl an Bienenvölkern",
                    "fr": "Nombre de colonies",
                    "ro": "Număr de colonii",
                    "pt": "Número de colónias",
                    "es": "Número de colonias de abejas melíferas",
                    "da": "Antal bifamilier"
                },
                "unit": null
            },
            {
                "id": 13,
                "type": "list",
                "name": "orientation",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "options",
                "trans": {
                    "en": "Orientation",
                    "nl": "Orientatie",
                    "de": "Orientierung",
                    "fr": "Orientation",
                    "ro": "Orientare",
                    "pt": "Orientação",
                    "es": "Orientación",
                    "da": "Retning"
                },
                "unit": null
            },
            {
                "id": 25,
                "type": "list",
                "name": "status",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "options",
                "trans": {
                    "en": "Status",
                    "nl": "Status",
                    "de": "Status",
                    "fr": "Statut",
                    "ro": "Stare",
                    "pt": "Estado",
                    "es": "Estado",
                    "da": "Status"
                },
                "unit": null
            },
            {
                "id": 28,
                "type": "system",
                "name": "photo",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "image",
                "trans": {
                    "en": "Photo",
                    "nl": "Foto",
                    "de": "Foto",
                    "fr": "Photo",
                    "ro": "Poză",
                    "pt": "Fotografia",
                    "es": "Foto",
                    "da": "Foto"
                },
                "unit": null
            },
            {
                "id": 913,
                "type": null,
                "name": "can_be_removed",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "list",
                "trans": {
                    "de": "kann entfernt werden",
                    "ro": "poate fi înlăturat",
                    "es": "Puede ser removido"
                },
                "unit": null
            },
            {
                "id": 932,
                "type": "checklist",
                "name": "type",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "select",
                "trans": {
                    "en": "Type",
                    "nl": "Type",
                    "de": "Typ",
                    "fr": "type",
                    "ro": "Tip",
                    "pt": "Tipo",
                    "es": "Tipo",
                    "da": "Type"
                },
                "unit": null
            }
        ]
    },
    {
        "id": 29,
        "type": "system",
        "name": "hive",
        "icon": null,
        "source": null,
        "required": 0,
        "input": "list",
        "trans": {
            "en": "Hive",
            "nl": "Kast",
            "de": "Beute",
            "fr": "Ruche",
            "ro": "Stup",
            "pt": "Colmeia",
            "es": "Colmena",
            "da": "Stade"
        },
        "unit": null,
        "children": [
            {
                "id": 30,
                "type": "system",
                "name": "id",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "ID",
                    "nl": "ID",
                    "de": "ID",
                    "fr": "ID",
                    "ro": "ID",
                    "pt": "ID",
                    "es": "ID",
                    "da": "ID"
                },
                "unit": null
            },
            {
                "id": 34,
                "type": "system",
                "name": "type",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Type",
                    "nl": "Type",
                    "de": "Typ",
                    "fr": "type",
                    "ro": "Tip",
                    "pt": "Tipo",
                    "es": "Tipo",
                    "da": "Type"
                },
                "unit": null
            },
            {
                "id": 64,
                "type": "system",
                "name": "frame_size",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Frame size",
                    "nl": "Raam afmetingen",
                    "de": "Rähmchengröße",
                    "fr": "Taille des cadres",
                    "ro": "Dimensiune ramă",
                    "pt": "Tamanho do quadro",
                    "es": "Tamaño de marco",
                    "da": "Rammestørrelse"
                },
                "unit": null
            },
            {
                "id": 84,
                "type": "system",
                "name": "configuration",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Configuration",
                    "nl": "Samenstelling",
                    "de": "Konfiguration",
                    "fr": "Configuration",
                    "ro": "Configurație",
                    "pt": "Configuração",
                    "es": "Configuración",
                    "da": "Opbygning"
                },
                "unit": null
            },
            {
                "id": 136,
                "type": "system",
                "name": "location",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Location",
                    "nl": "Locatie",
                    "de": "Ort",
                    "fr": "Lieux",
                    "ro": "Locație",
                    "pt": "Localização",
                    "es": "Ubicación",
                    "da": "Lokation"
                },
                "unit": null
            },
            {
                "id": 614,
                "type": "checklist",
                "name": "weight",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "number_2_decimals",
                "trans": {
                    "en": "Weight",
                    "nl": "Gewicht",
                    "de": "Gewicht",
                    "fr": "Poids",
                    "ro": "Greutate",
                    "pt": "Peso",
                    "es": "Peso",
                    "da": "Vægt"
                },
                "unit": "kg"
            },
            {
                "id": 795,
                "type": "system",
                "name": "photo",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "image",
                "trans": {
                    "en": "Photo",
                    "nl": "Foto",
                    "de": "Foto",
                    "fr": "Photo",
                    "ro": "Poză",
                    "pt": "Fotografia",
                    "es": "Foto",
                    "da": "Foto"
                },
                "unit": null
            },
            {
                "id": 818,
                "type": "system",
                "name": "app",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "list",
                "trans": {
                    "nl": "App",
                    "en": "App",
                    "de": "App",
                    "fr": "App",
                    "ro": "Aplicație",
                    "pt": "Aplicação (app)",
                    "es": "App",
                    "da": "App"
                },
                "unit": null
            }
        ]
    },
    {
        "id": 149,
        "type": "list",
        "name": "bee_colony",
        "icon": null,
        "source": null,
        "required": 0,
        "input": "label",
        "trans": {
            "en": "Bee colony",
            "nl": "Bijenvolk",
            "de": "Bienenvolk",
            "fr": "Colonie",
            "ro": "Colonie de albine",
            "pt": "Colónia",
            "es": "Colonia de abejas",
            "da": "Bifamilie"
        },
        "unit": null,
        "children": [
            {
                "id": 73,
                "type": "checklist",
                "name": "space",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Space",
                    "nl": "Ruimte",
                    "de": "Platz",
                    "fr": "Espacement",
                    "ro": "Spațiu",
                    "pt": "Espaço",
                    "es": "Espacio",
                    "da": "Mellemrum"
                },
                "unit": null
            },
            {
                "id": 150,
                "type": "system",
                "name": "origin",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Origin",
                    "nl": "Oorsprong",
                    "de": "Ursprung",
                    "fr": "Origine",
                    "ro": "Origine",
                    "pt": "Origem",
                    "es": "Origen",
                    "da": "Oprindelse"
                },
                "unit": null
            },
            {
                "id": 165,
                "type": "list",
                "name": "activity",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Activity",
                    "nl": "Activiteit",
                    "de": "Aktivität",
                    "fr": "Activité",
                    "ro": "Activitate",
                    "pt": "Actividade",
                    "es": "Actividad",
                    "da": "Aktivitet"
                },
                "unit": null
            },
            {
                "id": 208,
                "type": "system",
                "name": "status",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Status",
                    "nl": "Status",
                    "de": "Status",
                    "fr": "Statut",
                    "ro": "Stare",
                    "pt": "Estado",
                    "es": "Estado",
                    "da": "Status"
                },
                "unit": null
            },
            {
                "id": 213,
                "type": "list",
                "name": "characteristics",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Characteristics",
                    "nl": "Eigenschappen",
                    "de": "Charakteristiken",
                    "fr": "Caracteristique",
                    "ro": "Caracteristici",
                    "pt": "Características",
                    "es": "Características",
                    "da": "Egenskaber"
                },
                "unit": null
            },
            {
                "id": 253,
                "type": "checklist",
                "name": "swarm_prevention",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Swarm prevention",
                    "nl": "Zwermverhindering",
                    "de": "Schwarmverhinderung",
                    "fr": "Prévention de l'essaimage",
                    "ro": "Prevenirea roirii",
                    "pt": "Prevenção de enxameamento",
                    "es": "Prevención de enjambrazón",
                    "da": "Sværmehindring"
                },
                "unit": null
            },
            {
                "id": 263,
                "type": "list",
                "name": "brood",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Brood",
                    "nl": "Broed",
                    "de": "Brut",
                    "fr": "Couvain",
                    "ro": "Puiet",
                    "pt": "Criação",
                    "es": "Cría",
                    "da": "Yngel"
                },
                "unit": null
            },
            {
                "id": 333,
                "type": "checklist",
                "name": "queen",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Queen",
                    "nl": "Moer",
                    "de": "Königin",
                    "fr": "Reine",
                    "ro": "Matcă",
                    "pt": "Raínha",
                    "es": "Reina",
                    "da": "Dronning"
                },
                "unit": null
            },
            {
                "id": 442,
                "type": "list",
                "name": "drones",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Drones",
                    "nl": "Darren",
                    "de": "Drohnen",
                    "fr": "Mâles",
                    "ro": "Trântori",
                    "pt": "Zangões",
                    "es": "Zánganos",
                    "da": "Droner"
                },
                "unit": null
            },
            {
                "id": 448,
                "type": "checklist",
                "name": "uniting_colonies",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Uniting colonies",
                    "nl": "Volken samenvoegen",
                    "de": "Volksvereinigung",
                    "fr": "Reunion de colonies",
                    "ro": "Unificare colonii",
                    "pt": "União de colónias",
                    "es": "Colmenas fusionadas",
                    "da": "Samling af bifamilier"
                },
                "unit": null
            },
            {
                "id": 453,
                "type": "list",
                "name": "bees_added",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Bees added",
                    "nl": "Bijen toegevoegd",
                    "de": "Bienen hinzugefügt",
                    "fr": "Ajout d'abeille",
                    "ro": "Albine adăugate",
                    "pt": "Abelhas adicionadas",
                    "es": "Abejas agregadas",
                    "da": "Bier tilføjet"
                },
                "unit": null
            },
            {
                "id": 459,
                "type": "list",
                "name": "loss",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Loss",
                    "nl": "Verlies",
                    "de": "Verluste",
                    "fr": "Perdu",
                    "ro": "Pierderi",
                    "pt": "Perdas",
                    "es": "Pérdida",
                    "da": "Tab"
                },
                "unit": null
            },
            {
                "id": 472,
                "type": "list",
                "name": "removal",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Removal",
                    "nl": "Verwijdering",
                    "de": "Entfernt",
                    "fr": "Suppression",
                    "ro": "Înlăturare",
                    "pt": "Remoção",
                    "es": "Remoción",
                    "da": "Fjernelse"
                },
                "unit": null
            },
            {
                "id": 755,
                "type": "system",
                "name": "reminder",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "nl": "Herinnnering",
                    "en": "Reminder",
                    "de": "Erinnerung",
                    "fr": "Rappel",
                    "ro": "Aducere aminte",
                    "pt": "Lembrete",
                    "es": "Recordatorio",
                    "da": "Påmindelse"
                },
                "unit": null
            },
            {
                "id": 771,
                "type": "checklist",
                "name": "size",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "nl": "Grootte",
                    "en": "Size",
                    "de": "Größe",
                    "fr": "Taille",
                    "ro": "Mărime",
                    "pt": "Tamanho",
                    "es": "Tamaño",
                    "da": "Størrelse"
                },
                "unit": null
            },
            {
                "id": 776,
                "type": "checklist",
                "name": "splitting_colony",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "nl": "Volk splitsen",
                    "en": "Splitting colony",
                    "de": "Aufgeteiltes Volk",
                    "fr": "Division de colonie",
                    "ro": "Împărțirea coloniei",
                    "pt": "Colónia desdobrada",
                    "es": "División de colonia",
                    "da": "Opdeling af bifamilie"
                },
                "unit": null
            },
            {
                "id": 867,
                "type": "system",
                "name": "purpose",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Purpose",
                    "nl": "Doel",
                    "de": "Zweck",
                    "fr": "Raison",
                    "ro": "Scop",
                    "pt": "Propósito",
                    "es": "Propósito",
                    "da": "Formål"
                },
                "unit": null
            },
            {
                "id": 960,
                "type": "checklist",
                "name": "comb_replacement",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Comb replacement",
                    "nl": "Raat vervanging",
                    "de": "Wabenerneuerung",
                    "fr": "Remplacement de rayon",
                    "ro": "Înlocuirea fagurelui",
                    "pt": "Substituição de favos",
                    "es": "Reemplazo de panal",
                    "da": "Tavleudskiftning"
                },
                "unit": null
            }
        ]
    },
    {
        "id": 475,
        "type": "list",
        "name": "food",
        "icon": null,
        "source": null,
        "required": 0,
        "input": "label",
        "trans": {
            "en": "Food",
            "nl": "Voedsel",
            "de": "Futter",
            "fr": "Nourriture",
            "ro": "Hrană",
            "pt": "Comida",
            "es": "Alimento",
            "da": "Føde"
        },
        "unit": null,
        "children": [
            {
                "id": 476,
                "type": "checklist",
                "name": "feeding",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Feeding",
                    "nl": "Bijvoeren",
                    "de": "Fütterung",
                    "fr": "Nourrissement",
                    "ro": "Hrănire",
                    "pt": "Alimentação",
                    "es": "Alimentación",
                    "da": "Fodring"
                },
                "unit": null
            },
            {
                "id": 493,
                "type": "checklist",
                "name": "stock",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Stock",
                    "nl": "Voorraad",
                    "de": "Vorrat",
                    "fr": "Stock",
                    "ro": "Stoc",
                    "pt": "Stock",
                    "es": "Stock",
                    "da": "Lager"
                },
                "unit": null
            },
            {
                "id": 500,
                "type": "list",
                "name": "forage",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Forage",
                    "nl": "Dracht",
                    "de": "Futter",
                    "fr": "Butinage",
                    "ro": "Cules",
                    "pt": "Forrageamento",
                    "es": "Forraje"
                },
                "unit": null
            },
            {
                "id": 891,
                "type": "checklist",
                "name": "water",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Water",
                    "nl": "Water",
                    "de": "Wasser",
                    "fr": "Eau",
                    "ro": "Apă",
                    "pt": "Água",
                    "es": "Agua",
                    "da": "Vand"
                },
                "unit": null
            }
        ]
    },
    {
        "id": 513,
        "type": "list",
        "name": "disorder",
        "icon": null,
        "source": null,
        "required": 0,
        "input": "label",
        "trans": {
            "en": "Disorder",
            "nl": "Aandoening",
            "de": "Störung",
            "fr": "Problème",
            "ro": "Boală",
            "pt": "Problemas",
            "es": "Problema",
            "da": "Sygdom"
        },
        "unit": null,
        "children": [
            {
                "id": 514,
                "type": "list",
                "name": "type",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Type",
                    "nl": "Type",
                    "de": "Typ",
                    "fr": "type",
                    "ro": "Tip",
                    "pt": "Tipo",
                    "es": "Tipo",
                    "da": "Type"
                },
                "unit": null
            },
            {
                "id": 582,
                "type": "list",
                "name": "severity",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "score_amount",
                "trans": {
                    "en": "Severity",
                    "nl": "Ernst",
                    "de": "Schweregrad",
                    "fr": "Sévérité",
                    "ro": "Severitate",
                    "pt": "Severidade",
                    "da": "Alvorlighed"
                },
                "unit": null
            },
            {
                "id": 589,
                "type": "checklist",
                "name": "laboratory_test",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Laboratory test",
                    "nl": "Laboratorium test",
                    "de": "Labortest",
                    "fr": "Test laboratoire",
                    "ro": "Test de laborator",
                    "pt": "Teste laboratorial",
                    "es": "Test de laboratorio",
                    "da": "Laboratorietest"
                },
                "unit": null
            },
            {
                "id": 594,
                "type": "list",
                "name": "treatment",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Treatment",
                    "nl": "Behandeling",
                    "de": "Behandlung",
                    "fr": "Traitement",
                    "ro": "Tratament",
                    "pt": "Tratamento",
                    "es": "Tratamiento",
                    "da": "Behandling"
                },
                "unit": null
            },
            {
                "id": 830,
                "type": "checklist",
                "name": "varroa",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Varroa",
                    "nl": "Varroa",
                    "de": "Varoa",
                    "fr": "Varroa",
                    "ro": "Varroa",
                    "pt": "Varroa",
                    "es": "Varroa",
                    "da": "Varroa"
                },
                "unit": null
            }
        ]
    },
    {
        "id": 612,
        "type": "checklist",
        "name": "weather",
        "icon": null,
        "source": null,
        "required": 0,
        "input": "label",
        "trans": {
            "en": "Weather",
            "nl": "Weer",
            "de": "Wetter",
            "fr": "Météo",
            "ro": "Vreme",
            "pt": "Clima",
            "es": "Tiempo atmosférico",
            "da": "Vejr"
        },
        "unit": null,
        "children": [
            {
                "id": 615,
                "type": "checklist",
                "name": "ambient_temperature",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "number",
                "trans": {
                    "en": "Ambient temperature",
                    "nl": "Omgevingstemperatuur",
                    "de": "Umgebungstemperatur",
                    "fr": "Température ambiante",
                    "ro": "Temperatura ambientală",
                    "pt": "Temperatura ambiente",
                    "es": "Temperatura ambiental",
                    "da": "Omgivelsestemperatur"
                },
                "unit": "°C"
            },
            {
                "id": 620,
                "type": "checklist",
                "name": "humidity",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "number_percentage",
                "trans": {
                    "en": "Humidity",
                    "nl": "Luchtvochtigheid",
                    "de": "Feuchtigkeit",
                    "fr": "Humidité",
                    "ro": "Umiditate",
                    "pt": "Humidade",
                    "es": "Humedad",
                    "da": "Fugtighed"
                },
                "unit": "%"
            },
            {
                "id": 621,
                "type": "checklist",
                "name": "cloud_cover",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "options",
                "trans": {
                    "en": "Cloud cover",
                    "nl": "Wolken",
                    "de": "Wolkendecke",
                    "fr": "Couverture nuageuse",
                    "ro": "Nebulozitate",
                    "pt": "Nebulosidade",
                    "es": "Cubierto de nubes",
                    "da": "Skydække"
                },
                "unit": null
            },
            {
                "id": 628,
                "type": "checklist",
                "name": "wind",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "number",
                "trans": {
                    "en": "Wind",
                    "nl": "Wind",
                    "de": "Wind",
                    "fr": "Vent",
                    "ro": "Vânt",
                    "pt": "Vento",
                    "es": "Viento",
                    "da": "Vind"
                },
                "unit": "bft"
            },
            {
                "id": 629,
                "type": "checklist",
                "name": "precipitation",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "number_positive",
                "trans": {
                    "en": "Precipitation",
                    "nl": "Neerslag",
                    "de": "Niederschlag",
                    "fr": "Précipitation",
                    "ro": "Precipitații",
                    "pt": "Precipitação",
                    "es": "Precipitación",
                    "da": "Nedbør"
                },
                "unit": "mm"
            }
        ]
    },
    {
        "id": 658,
        "type": "system",
        "name": "beekeeper",
        "icon": null,
        "source": null,
        "required": 0,
        "input": "list",
        "trans": {
            "en": "Beekeeper",
            "nl": "Imker",
            "de": "Imker",
            "fr": "Apiculteur",
            "ro": "Apicultor",
            "pt": "Apicultor",
            "es": "Apicultor(a)",
            "da": "Biavler"
        },
        "unit": null,
        "children": [
            {
                "id": 659,
                "type": "0",
                "name": "name",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "boolean",
                "trans": {
                    "en": "Name",
                    "nl": "Naam",
                    "de": "Name",
                    "fr": "Nom",
                    "ro": "Nume",
                    "pt": "Nome",
                    "es": "Nombre",
                    "da": "Navn"
                },
                "unit": null
            },
            {
                "id": 660,
                "type": "list",
                "name": "location",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "select_location",
                "trans": {
                    "en": "Location",
                    "nl": "Locatie",
                    "de": "Ort",
                    "fr": "Lieux",
                    "ro": "Locație",
                    "pt": "Localização",
                    "es": "Ubicación",
                    "da": "Lokation"
                },
                "unit": null
            },
            {
                "id": 666,
                "type": "1",
                "name": "telephone",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "list_item",
                "trans": {
                    "en": "Telephone",
                    "nl": "Telefoon",
                    "de": "Telefon",
                    "fr": "Telephone",
                    "ro": "Telefon",
                    "pt": "Telefone",
                    "es": "Teléfono",
                    "da": "Telefon"
                },
                "unit": null
            },
            {
                "id": 667,
                "type": "2",
                "name": "email",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "list_item",
                "trans": {
                    "en": "Email",
                    "nl": "Email",
                    "de": "Email",
                    "fr": "Email",
                    "ro": "Email",
                    "pt": "Email",
                    "es": "Email",
                    "da": "Email"
                },
                "unit": null
            },
            {
                "id": 668,
                "type": "3",
                "name": "date_of_birth",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "date",
                "trans": {
                    "en": "Date of birth",
                    "nl": "Geboortedatum",
                    "de": "Geburtsdatum",
                    "fr": "Date de naissance",
                    "ro": "Data nașterii",
                    "pt": "Data de nascimento",
                    "es": "Fecha de Nacimiento",
                    "da": "Fødselsdato"
                },
                "unit": null
            },
            {
                "id": 669,
                "type": "4",
                "name": "gender",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "list_item",
                "trans": {
                    "en": "Gender",
                    "nl": "Geslacht",
                    "de": "Geschlecht",
                    "fr": "Genre",
                    "ro": "Gen",
                    "pt": "Género",
                    "es": "Género",
                    "da": "Køn"
                },
                "unit": null
            },
            {
                "id": 670,
                "type": "list",
                "name": "beekeeper_since",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Beekeeper since",
                    "nl": "Imker sinds",
                    "de": "Imker seit",
                    "fr": "Apiculteur depuis",
                    "ro": "Apicultor din",
                    "pt": "Apicultor desde",
                    "es": "Apicultor desde",
                    "da": "Biavler siden"
                },
                "unit": null
            },
            {
                "id": 672,
                "type": "5",
                "name": "beekeeper_id",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "text",
                "trans": {
                    "en": "Beekeeper ID",
                    "nl": "Imker ID",
                    "de": "Imker ID",
                    "fr": "ID apiculteur",
                    "ro": "ID apicultor",
                    "pt": "Número de apicultor",
                    "es": "ID Apicultor",
                    "da": "Biavler ID"
                },
                "unit": null
            },
            {
                "id": 673,
                "type": "list",
                "name": "company",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Company",
                    "nl": "Bedrijf",
                    "de": "Betrieb",
                    "fr": "Société",
                    "ro": "Companie",
                    "pt": "Empresa",
                    "es": "Compañía\/Empresa",
                    "da": "Firma"
                },
                "unit": null
            },
            {
                "id": 680,
                "type": "list",
                "name": "method",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "options",
                "trans": {
                    "en": "Method",
                    "nl": "Methode",
                    "de": "Methode",
                    "fr": "Méthode",
                    "ro": "Metodă",
                    "pt": "Método",
                    "es": "Método",
                    "da": "Metode"
                },
                "unit": null
            },
            {
                "id": 688,
                "type": "list",
                "name": "role",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "options",
                "trans": {
                    "en": "Role",
                    "nl": "Rol",
                    "fr": "Rôle",
                    "ro": "Rol",
                    "pt": "Papel",
                    "es": "Rol",
                    "da": "Rolle"
                },
                "unit": null
            },
            {
                "id": 691,
                "type": "system",
                "name": "photo",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "list_item",
                "trans": {
                    "en": "Photo",
                    "nl": "Foto",
                    "de": "Foto",
                    "fr": "Photo",
                    "ro": "Poză",
                    "pt": "Fotografia",
                    "es": "Foto",
                    "da": "Foto"
                },
                "unit": null
            },
            {
                "id": 692,
                "type": "list",
                "name": "inspection_role",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Inspection role",
                    "nl": "Inspectie rol",
                    "fr": "Rôle d'inspection",
                    "ro": "Scopul inspecției",
                    "pt": "Inspecção",
                    "es": "Rol durante la inspección"
                },
                "unit": null
            }
        ]
    },
    {
        "id": 698,
        "type": "list",
        "name": "production",
        "icon": null,
        "source": null,
        "required": 0,
        "input": "label",
        "trans": {
            "en": "Production",
            "nl": "Productie",
            "de": "Produktion",
            "fr": "Production",
            "ro": "Producție",
            "pt": "Produção",
            "es": "Producción",
            "da": "Produktion"
        },
        "unit": null,
        "children": [
            {
                "id": 851,
                "type": "checklist",
                "name": "honey",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Honey",
                    "nl": "Honing",
                    "de": "Honig",
                    "fr": "Miel",
                    "ro": "Miere",
                    "pt": "Mel",
                    "es": "Miel",
                    "da": "Honning"
                },
                "unit": null
            },
            {
                "id": 852,
                "type": "checklist",
                "name": "other",
                "icon": null,
                "source": null,
                "required": 0,
                "input": "label",
                "trans": {
                    "en": "Other",
                    "nl": "Andere",
                    "de": "andere",
                    "fr": "Autre",
                    "ro": "Alte",
                    "pt": "Outros",
                    "es": "Otro",
                    "da": "Andet"
                },
                "unit": null
            }
        ]
    }
]
```
> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/categories`


<!-- END_109013899e0bc43247b0f00b67f889cf -->

<!-- START_34925c1e31e7ecc53f8f52c8b1e91d44 -->
## api/categories/{id}
Display the specified category.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/categories/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/categories/1");

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
`GET api/categories/{category}`


<!-- END_34925c1e31e7ecc53f8f52c8b1e91d44 -->

<!-- START_1be6f19658a5b3020cc50ccdfefd249e -->
## api/categoryinputs
List of all available input types of the inspection categories

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/categoryinputs" 
```

```javascript
const url = new URL("https://api.beep.nl/api/categoryinputs");

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
[
    {
        "id": 1,
        "name": "List",
        "type": "list",
        "min": null,
        "max": null,
        "decimals": null
    },
    {
        "id": 2,
        "name": "List item",
        "type": "list_item",
        "min": null,
        "max": null,
        "decimals": null
    },
    {
        "id": 3,
        "name": "Boolean (yes = green)",
        "type": "boolean",
        "min": null,
        "max": null,
        "decimals": null
    }
]
```
> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/categoryinputs`


<!-- END_1be6f19658a5b3020cc50ccdfefd249e -->

#Api\ChecklistController


<!-- START_a822c84c5aed22599b5c0f93e2ce41c1 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/checklists" 
```

```javascript
const url = new URL("https://api.beep.nl/api/checklists");

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
`GET api/checklists`


<!-- END_a822c84c5aed22599b5c0f93e2ce41c1 -->

<!-- START_3e3f29f45650477fd4a18d781913f05b -->
## api/checklists
> Example request:

```bash
curl -X POST "https://api.beep.nl/api/checklists" 
```

```javascript
const url = new URL("https://api.beep.nl/api/checklists");

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
curl -X GET -G "https://api.beep.nl/api/checklists/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/checklists/1");

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
`GET api/checklists/{checklist}`


<!-- END_c5e0350854444e0a45c0b055503dc0a4 -->

<!-- START_794ee76fd055e386962fe5ec27954b9d -->
## api/checklists/{checklist}
> Example request:

```bash
curl -X PUT "https://api.beep.nl/api/checklists/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/checklists/1");

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
curl -X DELETE "https://api.beep.nl/api/checklists/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/checklists/1");

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

#Api\DeviceController

Store and retreive Devices that produce measurements
<!-- START_1221b770dd464496433a0d3d92f88d37 -->
## api/devices/multiple POST
Store/update multiple Devices in an array of Device objects

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X POST "https://api.beep.nl/api/devices/multiple" \
    -H "Content-Type: application/json" \
    -d '{"id":18,"key":"quod","hardware_id":"quibusdam","name":"unde","hive_id":15,"type":"deleniti","last_message_received":"omnis","firmware_version":"ipsa","hardware_version":"odio","boot_count":10,"measurement_interval_min":262594122.68668693,"measurement_transmission_ratio":29.891078,"ble_pin":"optio","battery_voltage":14,"next_downlink_message":"voluptatibus","last_downlink_result":"quas"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/devices/multiple");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "id": 18,
    "key": "quod",
    "hardware_id": "quibusdam",
    "name": "unde",
    "hive_id": 15,
    "type": "deleniti",
    "last_message_received": "omnis",
    "firmware_version": "ipsa",
    "hardware_version": "odio",
    "boot_count": 10,
    "measurement_interval_min": 262594122.68668693,
    "measurement_transmission_ratio": 29.891078,
    "ble_pin": "optio",
    "battery_voltage": 14,
    "next_downlink_message": "voluptatibus",
    "last_downlink_result": "quas"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/devices/multiple`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    id | integer |  optional  | Device id to update. (Required without key and hardware_id)
    key | string |  optional  | DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint. (Required without id and hardware_id)
    hardware_id | string |  optional  | Hardware id of the device as device name in TTN. (Required without id and key)
    name | string |  optional  | Device name
    hive_id | integer |  optional  | Hive that the sensor is measuring. Default: null
    type | string |  optional  | Category name of the hive type from the Categories table. Default: beep
    last_message_received | timestamp |  optional  | Will be converted with date('Y-m-d H:i:s', $last_message_received); before storing
    firmware_version | string |  optional  | Firmware version of the Device
    hardware_version | string |  optional  | Hardware version of the Device
    boot_count | integer |  optional  | Amount of boots of the Device
    measurement_interval_min | float |  optional  | Measurement interval in minutes
    measurement_transmission_ratio | float |  optional  | Measurements ratio of non-transmitted vs transmitted messages. If 0 or 1, every measurement gets transmitted.
    ble_pin | string |  optional  | Bleutooth PIN of Device: 6 numbers between 0-9
    battery_voltage | float |  optional  | Last measured battery voltage
    next_downlink_message | string |  optional  | Hex string to send via downlink at next connection (LoRaWAN port 6)
    last_downlink_result | string |  optional  | Result received from BEEP base after downlink message (LoRaWAN port 5)

<!-- END_1221b770dd464496433a0d3d92f88d37 -->

<!-- START_1e05262d240dcd7c5c277be4411cdd41 -->
## api/devices/ttn/{dev_id} GET
Get a BEEP TTS Cloud Device by Device ID (BEEP hardware_id)

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/devices/ttn/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/devices/ttn/1");

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
`GET api/devices/ttn/{dev_id}`


<!-- END_1e05262d240dcd7c5c277be4411cdd41 -->

<!-- START_52c49726ab742e3eb07b25ed65074e78 -->
## api/devices/ttn/{dev_id} POST
Create a BEEP TTS Cloud Device by Device ID, lorawan_device.dev_eui, and lorawan_device.app_key

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X POST "https://api.beep.nl/api/devices/ttn/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/devices/ttn/1");

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
`POST api/devices/ttn/{dev_id}`


<!-- END_52c49726ab742e3eb07b25ed65074e78 -->

<!-- START_8f41217bf023ddef5d8995d7c7c7e2e2 -->
## api/devices GET
List all user Devices

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/devices" \
    -H "Content-Type: application/json" \
    -d '{"hardware_id":"voluptate"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/devices");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "hardware_id": "voluptate"
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
[
    {
        "id": 1,
        "hive_id": 2,
        "name": "BEEPBASE-0000",
        "key": "000000000000000",
        "created_at": "2020-01-22 09:43:03",
        "last_message_received": null,
        "hardware_id": null,
        "firmware_version": null,
        "hardware_version": null,
        "boot_count": null,
        "measurement_interval_min": null,
        "measurement_transmission_ratio": null,
        "ble_pin": null,
        "battery_voltage": null,
        "next_downlink_message": null,
        "last_downlink_result": null,
        "type": "beep",
        "hive_name": "Hive 2",
        "location_name": "Test stand 1",
        "owner": true,
        "sensor_definitions": [
            {
                "id": 7,
                "name": null,
                "inside": null,
                "offset": 8131,
                "multiplier": null,
                "input_measurement_id": 7,
                "output_measurement_id": 20,
                "device_id": 1,
                "input_abbr": "w_v",
                "output_abbr": "weight_kg"
            }
        ]
    }
]
```
> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/devices`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    hardware_id | string |  optional  | Provide to filter on hardware_id

<!-- END_8f41217bf023ddef5d8995d7c7c7e2e2 -->

<!-- START_dbb21adf8d2a4b13a3c3113b57e8d329 -->
## api/devices POST
Create or Update a Device

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X POST "https://api.beep.nl/api/devices" \
    -H "Content-Type: application/json" \
    -d '{"id":11,"key":"quia","hardware_id":"iusto","name":"aspernatur","hive_id":4,"type":"aliquam","last_message_received":"magnam","firmware_version":"quis","hardware_version":"repudiandae","boot_count":18,"measurement_interval_min":20.00104,"measurement_transmission_ratio":2649103.8,"ble_pin":"est","battery_voltage":404.1277116,"next_downlink_message":"labore","last_downlink_result":"omnis","create_ttn_device":false,"app_key":"consequatur"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/devices");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "id": 11,
    "key": "quia",
    "hardware_id": "iusto",
    "name": "aspernatur",
    "hive_id": 4,
    "type": "aliquam",
    "last_message_received": "magnam",
    "firmware_version": "quis",
    "hardware_version": "repudiandae",
    "boot_count": 18,
    "measurement_interval_min": 20.00104,
    "measurement_transmission_ratio": 2649103.8,
    "ble_pin": "est",
    "battery_voltage": 404.1277116,
    "next_downlink_message": "labore",
    "last_downlink_result": "omnis",
    "create_ttn_device": false,
    "app_key": "consequatur"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/devices`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    id | integer |  optional  | Device id to update. (Required without key and hardware_id)
    key | string |  optional  | DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint. (Required without id and hardware_id)
    hardware_id | string |  optional  | Hardware id of the device as device name in TTN. (Required without id and key)
    name | string |  optional  | Device name
    hive_id | integer |  optional  | Hive that the sensor is measuring. Default: null
    type | string |  optional  | Category name of the hive type from the Categories table. Default: beep
    last_message_received | timestamp |  optional  | Will be converted with date('Y-m-d H:i:s', $last_message_received); before storing
    firmware_version | string |  optional  | Firmware version of the Device
    hardware_version | string |  optional  | Hardware version of the Device
    boot_count | integer |  optional  | Amount of boots of the Device
    measurement_interval_min | float |  optional  | Measurement interval in minutes
    measurement_transmission_ratio | float |  optional  | Measurements ratio of non-transmitted vs transmitted messages. If 0 or 1, every measurement gets transmitted.
    ble_pin | string |  optional  | Bleutooth PIN of Device: 6 numbers between 0-9
    battery_voltage | float |  optional  | Last measured battery voltage
    next_downlink_message | string |  optional  | Hex string to send via downlink at next connection (LoRaWAN port 6)
    last_downlink_result | string |  optional  | Result received from BEEP base after downlink message (LoRaWAN port 5)
    create_ttn_device | boolean |  optional  | If true, create a new LoRaWAN device in the BEEP TTN console. If succesfull, create the device.
    app_key | string |  optional  | BEEP base LoRaWAN application key that you would like to store in TTN

<!-- END_dbb21adf8d2a4b13a3c3113b57e8d329 -->

<!-- START_b50165d5ab057bad7bcff39a66a40cf1 -->
## api/devices/{id} GET
List one Device by id

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/devices/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/devices/1");

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
`GET api/devices/{device}`


<!-- END_b50165d5ab057bad7bcff39a66a40cf1 -->

<!-- START_52b9480c37d5f861392515f99f114a2c -->
## api/devices PUT/PATCH
Update an existing Device

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X PUT "https://api.beep.nl/api/devices/1" \
    -H "Content-Type: application/json" \
    -d '{"id":11,"key":"voluptatibus","hardware_id":"et","name":"commodi","hive_id":5,"type":"aut","delete":true,"last_message_received":"voluptas","firmware_version":"quas","hardware_version":"sapiente","boot_count":9,"measurement_interval_min":27400.5214,"measurement_transmission_ratio":439.39,"ble_pin":"ducimus","battery_voltage":619.76547,"next_downlink_message":"cupiditate","last_downlink_result":"rem"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/devices/1");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "id": 11,
    "key": "voluptatibus",
    "hardware_id": "et",
    "name": "commodi",
    "hive_id": 5,
    "type": "aut",
    "delete": true,
    "last_message_received": "voluptas",
    "firmware_version": "quas",
    "hardware_version": "sapiente",
    "boot_count": 9,
    "measurement_interval_min": 27400.5214,
    "measurement_transmission_ratio": 439.39,
    "ble_pin": "ducimus",
    "battery_voltage": 619.76547,
    "next_downlink_message": "cupiditate",
    "last_downlink_result": "rem"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/devices/{device}`

`PATCH api/devices/{device}`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    id | integer |  optional  | Device id to update. (Required without key and hardware_id)
    key | string |  optional  | DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint. (Required without id and hardware_id)
    hardware_id | string |  optional  | Hardware id of the device as device name in TTN. (Required without id and key)
    name | string |  optional  | Name of the sensor
    hive_id | integer |  optional  | Hive that the sensor is measuring. Default: null
    type | string |  optional  | Category name of the hive type from the Categories table. Default: beep
    delete | boolean |  optional  | If true delete the sensor and all it's data in the Influx database
    last_message_received | timestamp |  optional  | Will be converted with date('Y-m-d H:i:s', $last_message_received); before storing
    firmware_version | string |  optional  | Firmware version of the Device
    hardware_version | string |  optional  | Hardware version of the Device
    boot_count | integer |  optional  | Amount of boots of the Device
    measurement_interval_min | float |  optional  | Measurement interval in minutes
    measurement_transmission_ratio | float |  optional  | Measurements ratio of non-transmitted vs transmitted messages. If 0 or 1, every measurement gets transmitted.
    ble_pin | string |  optional  | Bleutooth PIN of Device: 6 numbers between 0-9
    battery_voltage | float |  optional  | Last measured battery voltage
    next_downlink_message | string |  optional  | Hex string to send via downlink at next connection (LoRaWAN port 6)
    last_downlink_result | string |  optional  | Result received from BEEP base after downlink message (LoRaWAN port 5)

<!-- END_52b9480c37d5f861392515f99f114a2c -->

#Api\ExportController

Export all data to an Excel file by email (GDPR)
<!-- START_5d658b079229cb412abcf4dada818425 -->
## api/export/csv POST
Generate a CSV measurement data export from InfluxDB. Make sure not to load a too large timespan (i.e. &gt; 30 days), because the call will not succeed due to memory overload.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X POST "https://api.beep.nl/api/export/csv" \
    -H "Content-Type: application/json" \
    -d '{"device_id":"inventore","start":"2020-05-27 16:16","end":"2020-05-30 00:00","separator":";","measurements":"'am2315_t,am2315_h,mhz_co2'","link":true}'

```

```javascript
const url = new URL("https://api.beep.nl/api/export/csv");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "device_id": "inventore",
    "start": "2020-05-27 16:16",
    "end": "2020-05-30 00:00",
    "separator": ";",
    "measurements": "'am2315_t,am2315_h,mhz_co2'",
    "link": true
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/export/csv`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    device_id | required |  optional  | Device id to download data from
    start | date |  required  | Date for start of data export.
    end | date |  required  | Date for end of data export.
    separator | string |  optional  | Symbol that should be used to separate columns in CSV file.
    measurements | string |  optional  | Comma separated list of measurement types to load. If you want a lot of data (i.e. > 30 days), make sure not to load more than one measurement.
    link | boolean |  optional  | filled means: save the export to a file and provide the link, not filled means: output a text/html header with text containing the .csv content. Example:

<!-- END_5d658b079229cb412abcf4dada818425 -->

<!-- START_48911fc3cde4ec2d92e30c1511b44372 -->
## api/export
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/export" 
```

```javascript
const url = new URL("https://api.beep.nl/api/export");

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
`GET api/export`


<!-- END_48911fc3cde4ec2d92e30c1511b44372 -->

#Api\FlashLogController


<!-- START_a462a44a55ccd320cfb308e26fb98a04 -->
## api/flashlogs GET
Provide a list of the available flashlogs

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/flashlogs" 
```

```javascript
const url = new URL("https://api.beep.nl/api/flashlogs");

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
`GET api/flashlogs`


<!-- END_a462a44a55ccd320cfb308e26fb98a04 -->

<!-- START_edb399b94d510170b12e27085dea3f43 -->
## api/flashlogs/{id} GET
Provide the contents of the log_file_parsed property of the flashlog

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/flashlogs/1?id=ea" \
    -H "Content-Type: application/json" \
    -d '{"matches_min":2,"match_props":7,"db_records":15,"block_id":1,"block_data_index":0,"data_minutes":5,"from_cache":false,"save_result":false,"csv":0,"json":0}'

```

```javascript
const url = new URL("https://api.beep.nl/api/flashlogs/1");

    let params = {
            "id": "ea",
        };
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "matches_min": 2,
    "match_props": 7,
    "db_records": 15,
    "block_id": 1,
    "block_data_index": 0,
    "data_minutes": 5,
    "from_cache": false,
    "save_result": false,
    "csv": 0,
    "json": 0
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
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
`GET api/flashlogs/{id}`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    matches_min | integer |  optional  | Flashlog minimum amount of inline measurements that should be matched. Default: 5.
    match_props | integer |  optional  | Flashlog minimum amount of measurement properties that should match. Default: 9.
    db_records | integer |  optional  | Flashlog minimum amount of inline measurements that should be matched. Default: 80.
    block_id | integer |  optional  | Flashlog block index to get both Flashlog as database data from.
    block_data_index | integer |  optional  | Flashlog data index to get both Flashlog as database data from.
    data_minutes | integer |  optional  | Flashlog data amount of minutes to show data from. Default: 10080 (1 week).
    from_cache | boolean |  optional  | get Flashlog parse result from cache (24 hours). Default: true.
    save_result | boolean |  optional  | Flashlog save the parsed result as new log_file_parsed. Default: false.
    csv | integer |  optional  | Save the Flashlog block_id data as a CSV file (1) and return a link. Default: 0.
    json | integer |  optional  | Save the Flashlog block_id data as a JSON file (1) and return a link. Default: 0.
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    id |  optional  | integer required Flashlog ID to parse

<!-- END_edb399b94d510170b12e27085dea3f43 -->

<!-- START_6882f99a56d783f2567bc9ee2e3fd575 -->
## api/flashlogs/{id} POST
Fill the missing database values with Flashlog values that match

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X POST "https://api.beep.nl/api/flashlogs/1?id=sequi" \
    -H "Content-Type: application/json" \
    -d '{"matches_min":2,"match_props":7,"db_records":15,"block_id":1,"from_cache":false,"save_result":false}'

```

```javascript
const url = new URL("https://api.beep.nl/api/flashlogs/1");

    let params = {
            "id": "sequi",
        };
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "matches_min": 2,
    "match_props": 7,
    "db_records": 15,
    "block_id": 1,
    "from_cache": false,
    "save_result": false
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/flashlogs/{id}`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    matches_min | integer |  optional  | Flashlog minimum amount of inline measurements that should be matched. Default: 5.
    match_props | integer |  optional  | Flashlog minimum amount of measurement properties that should match. Default: 9.
    db_records | integer |  optional  | Flashlog minimum amount of inline measurements that should be matched. Default: 80.
    block_id | integer |  optional  | Flashlog block index to get both Flashlog as database data from.
    from_cache | boolean |  optional  | get Flashlog parse result from cache (24 hours). Default: true.
    save_result | boolean |  optional  | Flashlog save the parsed result as new log_file_parsed. Default: false.
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    id |  optional  | integer required Flashlog ID to parse

<!-- END_6882f99a56d783f2567bc9ee2e3fd575 -->

<!-- START_a50bd4dcb083ae3926d0b60c28be145c -->
## api/flashlogs/{id} DELETE
Delete a block of data (block_id filled), or the whole Flashlog file

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X DELETE "https://api.beep.nl/api/flashlogs/1?id=voluptas" \
    -H "Content-Type: application/json" \
    -d '{"block_id":17}'

```

```javascript
const url = new URL("https://api.beep.nl/api/flashlogs/1");

    let params = {
            "id": "voluptas",
        };
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "block_id": 17
}

fetch(url, {
    method: "DELETE",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/flashlogs/{id}`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    block_id | integer |  optional  | Flashlog block index to delete (only the) previously persisted data from the database
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    id |  optional  | integer required Flashlog ID to delete the complete Flashlog file

<!-- END_a50bd4dcb083ae3926d0b60c28be145c -->

#Api\GroupController


<!-- START_d935edfdeccc953e11ef436a9d768e1c -->
## api/groups/checktoken POST
Check a token for a group id, and accept or decline the invite

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X POST "https://api.beep.nl/api/groups/checktoken" 
```

```javascript
const url = new URL("https://api.beep.nl/api/groups/checktoken");

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

<!-- START_007018a8a9f15c2d47fcb105431ffeee -->
## api/groups
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/groups" 
```

```javascript
const url = new URL("https://api.beep.nl/api/groups");

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
`GET api/groups`


<!-- END_007018a8a9f15c2d47fcb105431ffeee -->

<!-- START_15c22564ad248f952405021410fd1d25 -->
## api/groups
> Example request:

```bash
curl -X POST "https://api.beep.nl/api/groups" 
```

```javascript
const url = new URL("https://api.beep.nl/api/groups");

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
curl -X GET -G "https://api.beep.nl/api/groups/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/groups/1");

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
`GET api/groups/{group}`


<!-- END_a209a43173c7c4aaf7ab070d77fb7f0c -->

<!-- START_5b84408c838201930093112a7621935c -->
## api/groups/{group}
> Example request:

```bash
curl -X PUT "https://api.beep.nl/api/groups/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/groups/1");

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
curl -X DELETE "https://api.beep.nl/api/groups/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/groups/1");

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

<!-- START_e8fabede7787762b78140f2bfd317d77 -->
## api/groups/detach/{id}
> Example request:

```bash
curl -X DELETE "https://api.beep.nl/api/groups/detach/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/groups/detach/1");

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

#Api\HiveController


<!-- START_e5e611c362bfc5ad03913023307faa25 -->
## api/hives GET
Display a listing of user hives.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/hives" 
```

```javascript
const url = new URL("https://api.beep.nl/api/hives");

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
{
    "hives": [
        {
            "id": 1,
            "location_id": 1,
            "hive_type_id": 43,
            "color": "#35f200",
            "name": "Kast 1",
            "created_at": "2017-07-13 23:34:49",
            "type": "spaarkast",
            "location": "",
            "attention": null,
            "impression": null,
            "reminder": null,
            "reminder_date": null,
            "inspection_count": 0,
            "sensors": [
                3,
                19
            ],
            "owner": true,
            "layers": [
                {
                    "id": 1,
                    "order": 0,
                    "color": "#35f200",
                    "type": "brood",
                    "framecount": 10
                },
                {
                    "id": 2,
                    "order": 1,
                    "color": "#35f200",
                    "type": "brood",
                    "framecount": 10
                },
                {
                    "id": 3,
                    "order": 2,
                    "color": "#35f200",
                    "type": "honey",
                    "framecount": 10
                }
            ],
            "queen": null
        }
    ]
}
```
> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/hives`


<!-- END_e5e611c362bfc5ad03913023307faa25 -->

<!-- START_80df36a766c9f45b8245df7a4c584eef -->
## api/hives POST
Store a newly created Hive in storage for the authenticated user.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X POST "https://api.beep.nl/api/hives" 
```

```javascript
const url = new URL("https://api.beep.nl/api/hives");

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
## api/hives/{id} GET
Display the specified resource.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/hives/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/hives/1");

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
`GET api/hives/{hive}`


<!-- END_6ec9bb72691ffb3b668ce33a42d1f9a3 -->

<!-- START_e849cd4cd54a66f21f8716c15cfba36e -->
## api/hives/{id} PATCH
Update the specified user Hive in storage.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X PUT "https://api.beep.nl/api/hives/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/hives/1");

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
## api/hives/{id} DELETE
Remove the specified user Hive from storage.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X DELETE "https://api.beep.nl/api/hives/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/hives/1");

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

#Api\ImageController

Store and retreive image metadata (image_url, thumb_url, width, category_id, etc.)
<!-- START_8e05289fc079261819c2c145f89215f1 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/images" 
```

```javascript
const url = new URL("https://api.beep.nl/api/images");

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
`GET api/images`


<!-- END_8e05289fc079261819c2c145f89215f1 -->

<!-- START_204613676cab89a55dfdc7d81f16a281 -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://api.beep.nl/api/images" 
```

```javascript
const url = new URL("https://api.beep.nl/api/images");

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
`POST api/images`


<!-- END_204613676cab89a55dfdc7d81f16a281 -->

<!-- START_b72ae09f5d6ffe769e7e25847bfb4713 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/images/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/images/1");

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
`GET api/images/{image}`


<!-- END_b72ae09f5d6ffe769e7e25847bfb4713 -->

<!-- START_663d256882d5392cfe487383a4e8703e -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://api.beep.nl/api/images/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/images/1");

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
`PUT api/images/{image}`

`PATCH api/images/{image}`


<!-- END_663d256882d5392cfe487383a4e8703e -->

<!-- START_c75b2cb29db1089e35d664b3e14b03ca -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://api.beep.nl/api/images" 
```

```javascript
const url = new URL("https://api.beep.nl/api/images");

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
`DELETE api/images`


<!-- END_c75b2cb29db1089e35d664b3e14b03ca -->

#Api\InspectionsController


<!-- START_edd5f91a2b329960cacbe7483b5fd588 -->
## api/inspections GET
Show the &#039;inspections&#039; list with objects reflecting only the general inspection data.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/inspections" 
```

```javascript
const url = new URL("https://api.beep.nl/api/inspections");

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
`GET api/inspections`


<!-- END_edd5f91a2b329960cacbe7483b5fd588 -->

<!-- START_ccf630a0f3579abb6ab3d47b9f4a65ab -->
## api/inspections/lists GET
List checklists and its  inspections linked to Hive id. The &#039;inspections&#039; object contains a descending date ordered list of general inspection data. The &#039;items_by_date&#039; object contains a list of (rows of) inspection items that can be placed (in columns) under the inspections by created_at date (table format). NB: Use &#039;Accept-Language&#039; Header (default nl_NL) to provide localized category names (anc, name) in items_by_date.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/inspections/lists" \
    -H "Content-Type: application/json" \
    -d '{"id":17}'

```

```javascript
const url = new URL("https://api.beep.nl/api/inspections/lists");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "id": 17
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "checklists": [
        {
            "id": 810,
            "type": "beep_v2_copy",
            "name": "Beep v2 - info@beep.nl",
            "description": null,
            "created_at": "2020-01-13 18:30:02",
            "updated_at": "2020-01-13 19:58:47",
            "category_ids": [
                149,
                771,
                963,
                964,
                965,
                966,
                263,
                265,
                270,
                276
            ],
            "required_ids": [],
            "owner": true,
            "researches": []
        }
    ]
}
```
> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/inspections/lists`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    id | integer |  required  | The hive to request inspections from.

<!-- END_ccf630a0f3579abb6ab3d47b9f4a65ab -->

<!-- START_940d2b69b7a58b4becfea0c48da6b866 -->
## api/inspections/{id} GET
Show the &#039;inspection&#039; object. The object reflects only the general inspection data.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/inspections/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/inspections/1");

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
`GET api/inspections/{id}`


<!-- END_940d2b69b7a58b4becfea0c48da6b866 -->

<!-- START_aeffa3642b8d8eeca87b4c02f9b26262 -->
## api/inspections/hive/{hive_id} GET
List all inspections linked to Hive id. The &#039;inspections&#039; object contains a descending date ordered list of general inspection data. The &#039;items_by_date&#039; object contains a list of (rows of) inspection items that can be placed (in columns) under the inspections by created_at date (table format). NB: Use &#039;Accept-Language&#039; Header (default nl_NL) to provide localized category names (anc, name) in items_by_date.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/inspections/hive/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/inspections/hive/1");

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
{
    "inspections": [
        {
            "id": 93,
            "notes": null,
            "reminder": null,
            "reminder_date": null,
            "impression": 1,
            "attention": null,
            "created_at": "2020-05-18 12:34:00",
            "checklist_id": 829,
            "image_id": null,
            "owner": true,
            "thumb_url": null,
            "hive_id": 42
        },
        {
            "id": 91,
            "notes": null,
            "reminder": null,
            "reminder_date": null,
            "impression": 3,
            "attention": 0,
            "created_at": "2020-05-18 11:43:00",
            "checklist_id": 829,
            "image_id": null,
            "owner": true,
            "thumb_url": null,
            "hive_id": 42
        }
    ],
    "items_by_date": [
        {
            "anc": null,
            "name": "Bee colony",
            "items": null
        },
        {
            "anc": "Bee colony > Brood > ",
            "name": "Pattern consistency",
            "type": "score",
            "range": "min: 1 - max: 5",
            "items": [
                {
                    "id": 138,
                    "value": "3",
                    "inspection_id": 93,
                    "category_id": 279,
                    "val": "3",
                    "unit": null,
                    "type": "score"
                },
                ""
            ]
        },
        {
            "anc": "Bee colony > Brood > Status > ",
            "name": "All stages",
            "type": "boolean",
            "range": null,
            "items": [
                "",
                {
                    "id": 77,
                    "value": "1",
                    "inspection_id": 91,
                    "category_id": 868,
                    "val": "Yes",
                    "unit": null,
                    "type": "boolean"
                }
            ]
        },
        {
            "anc": "Bee colony > Brood > Status > ",
            "name": "Eggs",
            "type": "boolean",
            "range": null,
            "items": [
                "",
                {
                    "id": 308,
                    "value": "1",
                    "inspection_id": 91,
                    "category_id": 270,
                    "val": "Yes",
                    "unit": null,
                    "type": "boolean"
                }
            ]
        }
    ]
}
```
> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/inspections/hive/{hive_id}`


<!-- END_aeffa3642b8d8eeca87b4c02f9b26262 -->

<!-- START_eebc1891debfc7536a71b0f153ad21cd -->
## api/inspections POST
Register a new hive inspection the &#039;inspection&#039; object. The object reflects only the general inspection data.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X POST "https://api.beep.nl/api/inspections/store" \
    -H "Content-Type: application/json" \
    -d '{"date":"2020-05-18 16:16","items":"{\"547\":0,\"595\":1,\"845\":\"814\"}","hive_ids":"42","location_id":"2","id":15,"impression":-1,"attention":1,"reminder":"This is an inspection reminder","reminder_date":"2020-05-27 16:16","notes":"This is an inspection note","checklist_id":829}'

```

```javascript
const url = new URL("https://api.beep.nl/api/inspections/store");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "date": "2020-05-18 16:16",
    "items": "{\"547\":0,\"595\":1,\"845\":\"814\"}",
    "hive_ids": "42",
    "location_id": "2",
    "id": 15,
    "impression": -1,
    "attention": 1,
    "reminder": "This is an inspection reminder",
    "reminder_date": "2020-05-27 16:16",
    "notes": "This is an inspection note",
    "checklist_id": 829
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/inspections/store`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    date | date |  required  | The (local time) date time of the inspection.
    items | object |  required  | An object of category id's containing their inspected values (id's in case of lists, otherwise numeric/textual values).
    hive_ids | array |  required  | Array of Hive ids to which this inspection should be linked.
    location_id | Location |  optional  | id to which this inspection should be linked.
    id | integer |  optional  | If provided, edit and do not create inspection. Required to edit the inspection.
    impression | integer |  optional  | Numeric impression value -1 (unfilled) to 1-3 (smileys).
    attention | integer |  optional  | Numeric impression value -1 (unfilled) to 0-1 (needs attention).
    reminder | string |  optional  | Textual value of the reminder fields.
    reminder_date | date |  optional  | The (local time) date time for an optional reminder that can be fed to the users calender.
    notes | string |  optional  | Textual value of the notes fields.
    checklist_id | integer |  optional  | Id of the user checklist for generating this inspection.

<!-- END_eebc1891debfc7536a71b0f153ad21cd -->

<!-- START_78fa69f4fe2d3eed735b8d13151e5562 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://api.beep.nl/api/inspections/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/inspections/1");

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

#Api\LocationController

Manage Apiaries
<!-- START_7fb4739b1e26173b78c06ed910857f37 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/locations" 
```

```javascript
const url = new URL("https://api.beep.nl/api/locations");

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
`GET api/locations`


<!-- END_7fb4739b1e26173b78c06ed910857f37 -->

<!-- START_6ac6759cab929b9077bddc6d56416b5c -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://api.beep.nl/api/locations" 
```

```javascript
const url = new URL("https://api.beep.nl/api/locations");

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
curl -X GET -G "https://api.beep.nl/api/locations/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/locations/1");

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
`GET api/locations/{location}`


<!-- END_f71771a70af5f8dad2212b1b5a2258d5 -->

<!-- START_ddb58ef8759801169efb409d19aa45da -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://api.beep.nl/api/locations/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/locations/1");

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
curl -X DELETE "https://api.beep.nl/api/locations/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/locations/1");

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

#Api\MeasurementController

Store and retreive sensor data (both LoRa and API POSTs) from a Device
<!-- START_b3690d7048f832bb2ae7059b2ccd2d2e -->
## api/sensors POST
Store sensor measurement data (see BEEP sensor data API definition) from API, or TTN. See /sensors/measurement_types?locale=en which measurement types can be used to POST data to.

> Example request:

```bash
curl -X POST "https://api.beep.nl/api/sensors?key%2Fdata=aliquam" \
    -H "Content-Type: application/json" \
    -d '{"key\/data":"doloremque"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/sensors");

    let params = {
            "key/data": "aliquam",
        };
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "key\/data": "doloremque"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/sensors`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    key/data | json |  required  | Measurement data as JSON: {"key":"your_beep_device_key", "t":18.4, t_i":34.5, "weight_kg":57.348, "h":58, "bv":3.54}
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    key/data |  optional  | string required Measurement formatted as URL query: key=your_beep_device_key&t=18.4&t_i=34.5&weight_kg=57.348&h=58&bv=3.54

<!-- END_b3690d7048f832bb2ae7059b2ccd2d2e -->

<!-- START_493153e007f201a68c09b94906fd38fd -->
## api/lora_sensors POST
Store sensor measurement data (see BEEP sensor data API definition) from TTN or KPN (Simpoint)
When Simpoint payload is supplied, the LoRa HEX to key/value pairs decoding is done within function $this-&gt;parse_ttn_payload()
When TTN payload is supplied, the TTN HTTP integration decoder/converter is assumed to have already converted the payload from LoRa HEX to key/value conversion

> Example request:

```bash
curl -X POST "https://api.beep.nl/api/lora_sensors" \
    -H "Content-Type: application/json" \
    -d '{"key":"accusantium","payload_raw":"explicabo","payload_fields":"temporibus","DevEUI_uplink":"quae"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/lora_sensors");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "key": "accusantium",
    "payload_raw": "explicabo",
    "payload_fields": "temporibus",
    "DevEUI_uplink": "quae"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/lora_sensors`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    key | string |  required  | DEV EUI of the Device to enable storing sensor data
    payload_raw | string |  optional  | TTN BEEP Measurement data in Base 64 encoded string
    payload_fields | json |  optional  | TTN Measurement data array
    DevEUI_uplink | json |  optional  | KPN Measurement data array

<!-- END_493153e007f201a68c09b94906fd38fd -->

<!-- START_c3342d8fcf8e60d79307f7cd57c903d5 -->
## api/sensors/measurement_types GET
Request all currently available sensor measurement types that can be POSTed to

> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/sensors/measurement_types?locale=en" 
```

```javascript
const url = new URL("https://api.beep.nl/api/sensors/measurement_types");

    let params = {
            "locale": "en",
        };
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

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
{
    "t": "Temperatuur",
    "h": "Luchtvochtigheid",
    "p": "Luchtdruk",
    "w": "Weight",
    "l": "Licht",
    "bv": "Batterijvoltage",
    "w_v": "W v",
    "w_fl": "W fl",
    "w_fr": "W fr",
    "w_bl": "W bl",
    "w_br": "W br",
    "s_fan_4": "S fan 4",
    "s_fan_6": "S fan 6",
    "s_fan_9": "S fan 9",
    "s_fly_a": "S fly a",
    "s_tot": "S tot",
    "t_i": "Temperatuur intern",
    "bc_i": "Bijen in",
    "bc_o": "Bijen uit",
    "weight_kg": "Gewicht",
    "weight_kg_corrected": "Gewicht gecorrigeerd",
    "rssi": "Signaalsterkte",
    "snr": "Signaal-ruisverhouding",
    "lat": "Breedtegraad",
    "lon": "Lengtegraad",
    "s_bin098_146Hz": "S bin098 146Hz",
    "s_bin146_195Hz": "S bin146 195Hz",
    "s_bin195_244Hz": "S bin195 244Hz",
    "s_bin244_293Hz": "S bin244 293Hz",
    "s_bin293_342Hz": "S bin293 342Hz",
    "s_bin342_391Hz": "S bin342 391Hz",
    "s_bin391_439Hz": "S bin391 439Hz",
    "s_bin439_488Hz": "S bin439 488Hz",
    "s_bin488_537Hz": "S bin488 537Hz",
    "s_bin537_586Hz": "S bin537 586Hz",
    "calibrating_weight": "Calibrating weight",
    "w_fl_kg_per_val": "W fl kg per val",
    "w_fr_kg_per_val": "W fr kg per val",
    "w_bl_kg_per_val": "W bl kg per val",
    "w_br_kg_per_val": "W br kg per val",
    "w_fl_offset": "W fl offset",
    "w_fr_offset": "W fr offset",
    "w_bl_offset": "W bl offset",
    "w_br_offset": "W br offset",
    "bc_tot": "Bijen totaal",
    "s_spl": "S spl",
    "h_i": "H i",
    "w_v_offset": "W v offset",
    "w_v_kg_per_val": "W v kg per val",
    "s_bin_0_201": "S bin 0 201",
    "s_bin_201_402": "S bin 201 402",
    "s_bin_402_602": "S bin 402 602",
    "s_bin_602_803": "S bin 602 803",
    "s_bin_803_1004": "S bin 803 1004",
    "s_bin_1004_1205": "S bin 1004 1205",
    "s_bin_1205_1406": "S bin 1205 1406",
    "s_bin_1406_1607": "S bin 1406 1607",
    "s_bin_1607_1807": "S bin 1607 1807",
    "s_bin_1807_2008": "S bin 1807 2008",
    "t_0": "T 0",
    "t_1": "T 1",
    "t_2": "T 2",
    "t_3": "T 3",
    "t_4": "T 4",
    "t_5": "T 5",
    "t_6": "T 6",
    "t_7": "T 7",
    "t_8": "T 8",
    "t_9": "T 9",
    "s_bin_122_173": "S bin 122 173",
    "s_bin_71_122": "S bin 71 122",
    "s_bin_173_224": "S bin 173 224",
    "s_bin_224_276": "S bin 224 276",
    "s_bin_276_327": "S bin 276 327",
    "s_bin_327_378": "S bin 327 378",
    "s_bin_378_429": "S bin 378 429",
    "s_bin_429_480": "S bin 429 480",
    "s_bin_480_532": "S bin 480 532",
    "s_bin_532_583": "S bin 532 583"
}
```

### HTTP Request
`GET api/sensors/measurement_types`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    locale |  optional  | string Two digit locale to get translated sensor measurement types.

<!-- END_c3342d8fcf8e60d79307f7cd57c903d5 -->

<!-- START_b549b494bd72c5ff39ed4ca232b30d1b -->
## api/sensors POST
Store sensor measurement data (see BEEP sensor data API definition) from API, or TTN. See /sensors/measurement_types?locale=en which measurement types can be used to POST data to.

> Example request:

```bash
curl -X POST "https://api.beep.nl/api/sensors_auth?key%2Fdata=earum" \
    -H "Content-Type: application/json" \
    -d '{"key\/data":"esse"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/sensors_auth");

    let params = {
            "key/data": "earum",
        };
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "key\/data": "esse"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/sensors_auth`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    key/data | json |  required  | Measurement data as JSON: {"key":"your_beep_device_key", "t":18.4, t_i":34.5, "weight_kg":57.348, "h":58, "bv":3.54}
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    key/data |  optional  | string required Measurement formatted as URL query: key=your_beep_device_key&t=18.4&t_i=34.5&weight_kg=57.348&h=58&bv=3.54

<!-- END_b549b494bd72c5ff39ed4ca232b30d1b -->

<!-- START_4f42521a60d8bb82d22897367044bff3 -->
## api/lora_sensors POST
Store sensor measurement data (see BEEP sensor data API definition) from TTN or KPN (Simpoint)
When Simpoint payload is supplied, the LoRa HEX to key/value pairs decoding is done within function $this-&gt;parse_ttn_payload()
When TTN payload is supplied, the TTN HTTP integration decoder/converter is assumed to have already converted the payload from LoRa HEX to key/value conversion

> Example request:

```bash
curl -X POST "https://api.beep.nl/api/lora_sensors_auth" \
    -H "Content-Type: application/json" \
    -d '{"key":"molestias","payload_raw":"sit","payload_fields":"voluptatem","DevEUI_uplink":"quia"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/lora_sensors_auth");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "key": "molestias",
    "payload_raw": "sit",
    "payload_fields": "voluptatem",
    "DevEUI_uplink": "quia"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/lora_sensors_auth`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    key | string |  required  | DEV EUI of the Device to enable storing sensor data
    payload_raw | string |  optional  | TTN BEEP Measurement data in Base 64 encoded string
    payload_fields | json |  optional  | TTN Measurement data array
    DevEUI_uplink | json |  optional  | KPN Measurement data array

<!-- END_4f42521a60d8bb82d22897367044bff3 -->

<!-- START_f110a838c139b465d2aa0fe28eced864 -->
## api/sensors/measurements GET
Request all sensor measurements from a certain interval (hour, day, week, month, year) and index (0=until now, 1=previous interval, etc.)

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/sensors/measurements" \
    -H "Content-Type: application/json" \
    -d '{"key":"est","id":4,"hive_id":10,"names":"non","interval":"est","relative_interval":18,"index":16,"start":"2020-05-27 16:16","end":"2020-05-30 00:00","weather":1,"timezone":"Europe\/Amsterdam"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/sensors/measurements");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "key": "est",
    "id": 4,
    "hive_id": 10,
    "names": "non",
    "interval": "est",
    "relative_interval": 18,
    "index": 16,
    "start": "2020-05-27 16:16",
    "end": "2020-05-30 00:00",
    "weather": 1,
    "timezone": "Europe\/Amsterdam"
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
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

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    key | string |  optional  | DEV EUI to look up the sensor (Device)
    id | integer |  optional  | ID to look up the sensor (Device)
    hive_id | integer |  optional  | Hive ID to look up the sensor (Device)
    names | string |  optional  | comma separated list of Measurement abbreviations to filter request data (weight_kg, t, h, etc.)
    interval | string |  optional  | Data interval for interpolation of measurement values: hour (2min), day (10min), week (1 hour), month (3 hours), year (1 day). Default: day.
    relative_interval | integer |  optional  | Load data from the selected interval relative to current time (1), or load data in absolute intervals (from start-end of hour/day/week/etc) (0). Default: 0.
    index | integer |  optional  | Interval index (>=0; 0=until now, 1=previous interval, etc.). Default: 0.
    start | date |  optional  | Date for start of measurements. Required without interval & index.
    end | date |  optional  | Date for end of measurements. Required without interval & index.
    weather | integer |  optional  | Load corresponding weather data from the weather database (1) or not (0).
    timezone | string |  optional  | Provide the front-end timezone to correct the time from UTC to front-end time.

<!-- END_f110a838c139b465d2aa0fe28eced864 -->

<!-- START_5ed0763eff49928fbff019838d73ffce -->
## api/sensors/lastvalues GET
Request last measurement values of all sensor measurements from a Device

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/sensors/lastvalues" \
    -H "Content-Type: application/json" \
    -d '{"key":"nam","id":3,"hive_id":8}'

```

```javascript
const url = new URL("https://api.beep.nl/api/sensors/lastvalues");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "key": "nam",
    "id": 3,
    "hive_id": 8
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
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
`GET api/sensors/lastvalues`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    key | string |  optional  | DEV EUI to look up the Device
    id | integer |  optional  | ID to look up the Device
    hive_id | integer |  optional  | Hive ID to look up the Device

<!-- END_5ed0763eff49928fbff019838d73ffce -->

<!-- START_cae771ed592df40719b12c9bcbf8409c -->
## api/sensors/lastweight GET
Request last weight related measurement values from a sensor (Device), used by legacy webapp to show calibration data: [&#039;w_fl&#039;, &#039;w_fr&#039;, &#039;w_bl&#039;, &#039;w_br&#039;, &#039;w_v&#039;, &#039;weight_kg&#039;, &#039;weight_kg_corrected&#039;, &#039;calibrating_weight&#039;, &#039;w_v_offset&#039;, &#039;w_v_kg_per_val&#039;, &#039;w_fl_offset&#039;, &#039;w_fr_offset&#039;, &#039;w_bl_offset&#039;, &#039;w_br_offset&#039;]

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/sensors/lastweight" \
    -H "Content-Type: application/json" \
    -d '{"key":"qui","id":11,"hive_id":6}'

```

```javascript
const url = new URL("https://api.beep.nl/api/sensors/lastweight");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "key": "qui",
    "id": 11,
    "hive_id": 6
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
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
`GET api/sensors/lastweight`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    key | string |  optional  | DEV EUI to look up the sensor (Device)
    id | integer |  optional  | ID to look up the sensor (Device)
    hive_id | integer |  optional  | Hive ID to look up the sensor (Device)

<!-- END_cae771ed592df40719b12c9bcbf8409c -->

<!-- START_6a462f7d1eb1f0e0377d2a3d717ab5f2 -->
## api/sensors/calibrateweight
Legacy method, used by legacy webapp to store weight calibration value e.g.[w_v_kg_per_val] in Influx database, to lookup and calculate [weight_kg] at incoming measurement value storage

At the next measurement coming in, calibrate each weight sensor with it's part of a given weight.
Because the measurements can come in only each hour/ 3hrs, set a value to trigger the calculation on next measurement

1. If $next_measurement == true: save 'calibrating' = true in Influx with the sensor key
2. If $next_measurement == false: save 'calibrating' = false in Influx with the sensor key and...
3.   Get the last measured weight values for this sensor key,
     Divide the given weight (in kg) with the amount of sensor values > 1.0 (assuming the weight is evenly distributed)
     Calculate the multiplier per sensor by dividing the multiplier = weight_part / (value - offset)
     Save the multiplier as $device_name.'_kg_per_val' in Influx

> Example request:

```bash
curl -X POST "https://api.beep.nl/api/sensors/calibrateweight" 
```

```javascript
const url = new URL("https://api.beep.nl/api/sensors/calibrateweight");

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
curl -X POST "https://api.beep.nl/api/sensors/offsetweight" 
```

```javascript
const url = new URL("https://api.beep.nl/api/sensors/offsetweight");

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

<!-- START_6a44743eac9c5405a257ac407399e973 -->
## api/sensors/measurement_types_available
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/sensors/measurement_types_available" 
```

```javascript
const url = new URL("https://api.beep.nl/api/sensors/measurement_types_available");

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
`GET api/sensors/measurement_types_available`


<!-- END_6a44743eac9c5405a257ac407399e973 -->

<!-- START_868e5915a367c3c473a7713a339e7863 -->
## api/sensors/flashlog
POST data from BEEP base fw 1.5.0+ FLASH log (with timestamp), interpret data and store in InlfuxDB (overwriting existing data). BEEP base BLE cmd: when the response is 200 OK and erase_mx_flash &gt; -1, provide the ERASE_MX_FLASH BLE command (0x21) to the BEEP base with the last byte being the HEX value of the erase_mx_flash value (0 = 0x00, 1 = 0x01, i.e.0x2100, or 0x2101, i.e. erase_type:&quot;fatfs&quot;, or erase_type:&quot;full&quot;)

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X POST "https://api.beep.nl/api/sensors/flashlog?show=labore&save=sunt&fill=incidunt&log_size_bytes=voluptatum" \
    -H "Content-Type: application/json" \
    -d '{"id":7,"key":"eligendi","hardware_id":"sunt","data":"aut","file":"fugit"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/sensors/flashlog");

    let params = {
            "show": "labore",
            "save": "sunt",
            "fill": "incidunt",
            "log_size_bytes": "voluptatum",
        };
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "id": 7,
    "key": "eligendi",
    "hardware_id": "sunt",
    "data": "aut",
    "file": "fugit"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
null
```

### HTTP Request
`POST api/sensors/flashlog`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    id | integer |  optional  | Device id to update. (Required without key and hardware_id)
    key | string |  optional  | DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint. (Required without id and hardware_id)
    hardware_id | string |  optional  | Hardware id of the device as device name in TTN. (Required without id and key)
    data | string |  optional  | MX_FLASH_LOG Hexadecimal string lines (new line) separated, with many rows of log data, or text file binary with all data inside.
    file | binary |  optional  | File with MX_FLASH_LOG Hexadecimal string lines (new line) separated, with many rows of log data, or text file binary with all data inside.
#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    show |  optional  | integer 1 for displaying info in result JSON, 0 for not displaying (default).
    save |  optional  | integer 1 for saving the data to a file (default), 0 for not save log file.
    fill |  optional  | integer 1 for filling data gaps in the database, 0 for not filling gaps (default).
    log_size_bytes |  optional  | integer 0x22 decimal result of log size requested from BEEP base.

<!-- END_868e5915a367c3c473a7713a339e7863 -->

<!-- START_98b04e5250f0142e7dff795ddfc22fb2 -->
## api/sensors/decode/p/{port}/pl/{payload}
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/sensors/decode/p/1/pl/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/sensors/decode/p/1/pl/1");

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
`GET api/sensors/decode/p/{port}/pl/{payload}`


<!-- END_98b04e5250f0142e7dff795ddfc22fb2 -->

#Api\QueenController

Not used
<!-- START_6617e742ea9a08d8b9eb9c7b254615de -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/queens" 
```

```javascript
const url = new URL("https://api.beep.nl/api/queens");

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
`GET api/queens`


<!-- END_6617e742ea9a08d8b9eb9c7b254615de -->

<!-- START_af48b21ffd9099e4fe417ced8d762f7a -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://api.beep.nl/api/queens" 
```

```javascript
const url = new URL("https://api.beep.nl/api/queens");

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
curl -X GET -G "https://api.beep.nl/api/queens/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/queens/1");

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
`GET api/queens/{queen}`


<!-- END_a92861a394debdbec3528ef3c26d8a22 -->

<!-- START_20c827dbbb9d7175c20551b24a9b21bc -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://api.beep.nl/api/queens/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/queens/1");

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
curl -X DELETE "https://api.beep.nl/api/queens/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/queens/1");

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

#Api\ResearchController


<!-- START_5f81999d6a12d44ae368b63e6fae439d -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/research" 
```

```javascript
const url = new URL("https://api.beep.nl/api/research");

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
`GET api/research`


<!-- END_5f81999d6a12d44ae368b63e6fae439d -->

<!-- START_5ba9f23a9188b99ee63b3014619f551c -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X POST "https://api.beep.nl/api/research/1/add_consent" 
```

```javascript
const url = new URL("https://api.beep.nl/api/research/1/add_consent");

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
curl -X POST "https://api.beep.nl/api/research/1/remove_consent" 
```

```javascript
const url = new URL("https://api.beep.nl/api/research/1/remove_consent");

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

<!-- START_63144b02283265650707aefc1f039183 -->
## api/research/{id}/edit/{consent_id}
> Example request:

```bash
curl -X PATCH "https://api.beep.nl/api/research/1/edit/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/research/1/edit/1");

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
`PATCH api/research/{id}/edit/{consent_id}`


<!-- END_63144b02283265650707aefc1f039183 -->

<!-- START_485051d7c03d1b4a19fc58cf42d8ad90 -->
## api/research/{id}/delete/{consent_id}
> Example request:

```bash
curl -X DELETE "https://api.beep.nl/api/research/1/delete/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/research/1/delete/1");

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
`DELETE api/research/{id}/delete/{consent_id}`


<!-- END_485051d7c03d1b4a19fc58cf42d8ad90 -->

#Api\ResearchDataController

Retreive owned or viewable Research data
<!-- START_65b256616f284693ece03ee6e9d2dd02 -->
## api/researchdata GET
List all available Researches

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/researchdata" 
```

```javascript
const url = new URL("https://api.beep.nl/api/researchdata");

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
[
    {
        "id": 1,
        "created_at": "2020-02-25 03:01:57",
        "updated_at": "2020-11-13 17:08:31",
        "name": "B-GOOD",
        "url": "https:\/\/b-good-project.eu\/",
        "description": "B-GOOD has the overall goal to provide guidance for beekeepers and help them make better and more informed decisions.",
        "type": "research-b-good",
        "institution": "Wageningen University & Research",
        "type_of_data_used": "Hive inspections, hive settings, BEEP base measurement data",
        "start_date": "2019-07-01 00:00:00",
        "end_date": "2023-06-30 00:00:00",
        "image_id": 1,
        "consent": true,
        "consent_history": [
            {
                "id": 185,
                "created_at": "2020-11-12 22:28:09",
                "updated_at": "2020-06-12 22:28:00",
                "user_id": 1,
                "research_id": 1,
                "consent": 1,
                "consent_location_ids": null,
                "consent_hive_ids": null,
                "consent_sensor_ids": null
            },
            {
                "id": 1,
                "created_at": "2020-02-25 03:02:23",
                "updated_at": "2020-05-27 03:03:00",
                "user_id": 1,
                "research_id": 1,
                "consent": 0,
                "consent_location_ids": null,
                "consent_hive_ids": null,
                "consent_sensor_ids": null
            },
            {
                "id": 97,
                "created_at": "2020-05-14 16:24:41",
                "updated_at": "2020-03-14 16:24:00",
                "user_id": 1,
                "research_id": 1,
                "consent": 1,
                "consent_location_ids": null,
                "consent_hive_ids": null,
                "consent_sensor_ids": null
            }
        ],
        "checklist_names": [
            "1 Winter",
            "2 Varroa",
            "3 Summer+",
            "4 Summer",
            "5 Health"
        ],
        "thumb_url": "\/storage\/users\/1\/thumbs\/research\/6LJEp35dodWWtfxnm3xfRnL05qvvJrHbn8IXAJqNCFZj2vFjwyLXbmWscKVz.jpg",
        "image": {
            "id": 1,
            "created_at": "2020-02-25 03:01:57",
            "updated_at": "2020-02-25 03:01:57",
            "filename": "6LJEp35dodWWtfxnm3xfRnL05qvvJrHbn8IXAJqNCFZj2vFjwyLXbmWscKVz.jpg",
            "image_url": "\/storage\/users\/1\/images\/research\/6LJEp35dodWWtfxnm3xfRnL05qvvJrHbn8IXAJqNCFZj2vFjwyLXbmWscKVz.jpg",
            "thumb_url": "\/storage\/users\/1\/thumbs\/research\/6LJEp35dodWWtfxnm3xfRnL05qvvJrHbn8IXAJqNCFZj2vFjwyLXbmWscKVz.jpg",
            "description": "B-GOOD has the overall goal to provide guidance for beekeepers and help them make better and more informed decisions.",
            "type": "research",
            "height": 1271,
            "width": 1271,
            "size_kb": 51,
            "date": "2020-02-25 03:01:57",
            "hive_id": null,
            "category_id": null,
            "inspection_id": null
        }
    }
]
```
> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/researchdata`


<!-- END_65b256616f284693ece03ee6e9d2dd02 -->

<!-- START_ac5a4e29917275abfbc2ec2e8fc1201b -->
## api/researchdata/{id} GET
List one Research by id with list of consent_users

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/researchdata/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/researchdata/1");

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
{
    "research": {
        "id": 1,
        "created_at": "2020-02-25 03:01:57",
        "updated_at": "2020-11-18 10:33:23",
        "name": "B-GOOD",
        "url": "https:\/\/b-good-project.eu\/",
        "description": "B-GOOD has the overall goal to provide guidance for beekeepers and help them make better and more informed decisions.",
        "type": "research-b-good",
        "institution": "Wageningen University & Research",
        "type_of_data_used": "Hive inspections, hive settings, BEEP base measurement data",
        "start_date": "2019-07-01 00:00:00",
        "end_date": "2023-06-30 00:00:00",
        "image_id": 1,
        "consent": true,
        "consent_history": [
            {
                "id": 185,
                "created_at": "2020-11-12 22:28:09",
                "updated_at": "2020-06-12 22:28:00",
                "user_id": 1,
                "research_id": 1,
                "consent": 1,
                "consent_location_ids": null,
                "consent_hive_ids": null,
                "consent_sensor_ids": null
            },
            {
                "id": 1,
                "created_at": "2020-02-25 03:02:23",
                "updated_at": "2020-05-27 03:03:00",
                "user_id": 1,
                "research_id": 1,
                "consent": 0,
                "consent_location_ids": null,
                "consent_hive_ids": null,
                "consent_sensor_ids": null
            },
            {
                "id": 97,
                "created_at": "2020-05-14 16:24:41",
                "updated_at": "2020-03-14 16:24:00",
                "user_id": 1,
                "research_id": 1,
                "consent": 1,
                "consent_location_ids": null,
                "consent_hive_ids": null,
                "consent_sensor_ids": null
            }
        ],
        "checklist_names": [
            "1 Winter",
            "2 Varroa",
            "3 Summer+",
            "4 Summer",
            "5 Health"
        ],
        "thumb_url": "\/storage\/users\/1\/thumbs\/research\/6LJEp35dodWWtfxnm3xfRnL05qvvJrHbn8IXAJqNCFZj2vFjwyLXbmWscKVz.jpg",
        "image": {
            "id": 1,
            "created_at": "2020-02-25 03:01:57",
            "updated_at": "2020-02-25 03:01:57",
            "filename": "6LJEp35dodWWtfxnm3xfRnL05qvvJrHbn8IXAJqNCFZj2vFjwyLXbmWscKVz.jpg",
            "image_url": "\/storage\/users\/1\/images\/research\/6LJEp35dodWWtfxnm3xfRnL05qvvJrHbn8IXAJqNCFZj2vFjwyLXbmWscKVz.jpg",
            "thumb_url": "\/storage\/users\/1\/thumbs\/research\/6LJEp35dodWWtfxnm3xfRnL05qvvJrHbn8IXAJqNCFZj2vFjwyLXbmWscKVz.jpg",
            "description": "B-GOOD has the overall goal to provide guidance for beekeepers and help them make better and more informed decisions.",
            "type": "research",
            "height": 1271,
            "width": 1271,
            "size_kb": 51,
            "date": "2020-02-25 03:01:57",
            "hive_id": null,
            "category_id": null,
            "inspection_id": null
        }
    },
    "consent_users": [
        {
            "id": 1,
            "name": "Beep",
            "email": "pim@beep.nl",
            "created_at": "2017-07-14 03:34:10",
            "updated_at": "2020-05-27 03:03:00",
            "last_login": "2020-11-18 10:32:16",
            "locale": null,
            "consent": 0
        },
        {
            "id": 2371,
            "name": "app@beep.nl",
            "email": "app@beep.nl",
            "created_at": "2019-10-24 17:15:55",
            "updated_at": "2020-02-25 11:46:59",
            "last_login": "2020-08-20 18:24:22",
            "locale": null,
            "consent": 0
        },
        {
            "id": 1,
            "name": "Beep",
            "email": "pim@beep.nl",
            "created_at": "2017-07-14 03:34:10",
            "updated_at": "2020-06-12 22:28:00",
            "last_login": "2020-11-18 10:32:16",
            "locale": null,
            "consent": 1
        }
    ]
}
```
> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/researchdata/{id}`


<!-- END_ac5a4e29917275abfbc2ec2e8fc1201b -->

<!-- START_4f4cc2a64151945d3e57be605dc0b98a -->
## api/researchdata/{id}/user/{user_id}/{item} GET
List all user &#039;item&#039; data within the consent=1 periods of a specific user within a Research. The &#039;item&#039; field indicates the type of user data (apiaries/hives/devices/flashlogs/inspections/measurements/weather) to request within the research (which the user gave consent for to use). Example: inspectionsResponse: api/researchdata/1/user/1/inspections.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/researchdata/1/user/1/1" \
    -H "Content-Type: application/json" \
    -d '{"date_start":"2020-01-01 00:00:00","date_until":"2020-09-29 23:59:59","device_id":1,"precision":"rfc3339"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/researchdata/1/user/1/1");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "date_start": "2020-01-01 00:00:00",
    "date_until": "2020-09-29 23:59:59",
    "device_id": 1,
    "precision": "rfc3339"
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
[
    {
        "id": 35211,
        "notes": "test",
        "reminder": null,
        "reminder_date": null,
        "impression": 2,
        "attention": 1,
        "created_at": "2020-03-26 18:28:00",
        "checklist_id": 798,
        "image_id": null,
        "owner": true,
        "thumb_url": null,
        "hive_id": 280,
        "items": []
    },
    {
        "id": 40162,
        "notes": "Input Liebefeld",
        "reminder": null,
        "reminder_date": null,
        "impression": null,
        "attention": null,
        "created_at": "2020-04-24 11:03:00",
        "checklist_id": 3206,
        "image_id": null,
        "owner": true,
        "thumb_url": null,
        "hive_id": 280,
        "items": [
            {
                "id": 326538,
                "value": "0.6",
                "inspection_id": 40162,
                "category_id": 977,
                "val": "0.6",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326539,
                "value": "4",
                "inspection_id": 40162,
                "category_id": 978,
                "val": "4",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326540,
                "value": "2",
                "inspection_id": 40162,
                "category_id": 979,
                "val": "2",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326541,
                "value": "4",
                "inspection_id": 40162,
                "category_id": 980,
                "val": "4",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326542,
                "value": "3",
                "inspection_id": 40162,
                "category_id": 981,
                "val": "3",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326543,
                "value": "581",
                "inspection_id": 40162,
                "category_id": 982,
                "val": "581",
                "unit": "bzz",
                "type": "number_positive"
            },
            {
                "id": 326544,
                "value": "5",
                "inspection_id": 40162,
                "category_id": 984,
                "val": "5",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326545,
                "value": "1",
                "inspection_id": 40162,
                "category_id": 985,
                "val": "1",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326546,
                "value": "4",
                "inspection_id": 40162,
                "category_id": 987,
                "val": "4",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326547,
                "value": "5",
                "inspection_id": 40162,
                "category_id": 988,
                "val": "5",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326548,
                "value": "4",
                "inspection_id": 40162,
                "category_id": 989,
                "val": "4",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326549,
                "value": "2",
                "inspection_id": 40162,
                "category_id": 990,
                "val": "2",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326550,
                "value": "2",
                "inspection_id": 40162,
                "category_id": 991,
                "val": "2",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326551,
                "value": "3",
                "inspection_id": 40162,
                "category_id": 992,
                "val": "3",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326552,
                "value": "3",
                "inspection_id": 40162,
                "category_id": 993,
                "val": "3",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326553,
                "value": "6",
                "inspection_id": 40162,
                "category_id": 995,
                "val": "6",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326554,
                "value": "2",
                "inspection_id": 40162,
                "category_id": 996,
                "val": "2",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326555,
                "value": "2",
                "inspection_id": 40162,
                "category_id": 997,
                "val": "2",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326556,
                "value": "2",
                "inspection_id": 40162,
                "category_id": 998,
                "val": "2",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326557,
                "value": "2",
                "inspection_id": 40162,
                "category_id": 999,
                "val": "2",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326558,
                "value": "2",
                "inspection_id": 40162,
                "category_id": 1000,
                "val": "2",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326559,
                "value": "2",
                "inspection_id": 40162,
                "category_id": 1001,
                "val": "2",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326560,
                "value": "8",
                "inspection_id": 40162,
                "category_id": 1163,
                "val": "8",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326561,
                "value": "4",
                "inspection_id": 40162,
                "category_id": 1164,
                "val": "4",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326562,
                "value": "2",
                "inspection_id": 40162,
                "category_id": 1165,
                "val": "2",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326563,
                "value": "6",
                "inspection_id": 40162,
                "category_id": 1166,
                "val": "6",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326564,
                "value": "2",
                "inspection_id": 40162,
                "category_id": 1167,
                "val": "2",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326565,
                "value": "2",
                "inspection_id": 40162,
                "category_id": 1168,
                "val": "2",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            },
            {
                "id": 326566,
                "value": "3",
                "inspection_id": 40162,
                "category_id": 1169,
                "val": "3",
                "unit": "x 25cm2",
                "type": "square_25cm2"
            }
        ]
    },
    {
        "id": 40163,
        "notes": "Brood photograph",
        "reminder": null,
        "reminder_date": null,
        "impression": null,
        "attention": null,
        "created_at": "2020-04-24 11:07:00",
        "checklist_id": 3206,
        "image_id": null,
        "owner": true,
        "thumb_url": null,
        "hive_id": 280,
        "items": [
            {
                "id": 326567,
                "value": "1",
                "inspection_id": 40163,
                "category_id": 399,
                "val": "Ja",
                "unit": null,
                "type": "boolean"
            },
            {
                "id": 326568,
                "value": "https:\/\/assets.beep.nl\/users\/1\/thumbs\/inspection\/jIcycTYnO8zYq6SHCvAwPHb97BDLFkZaDmfZUop5.png",
                "inspection_id": 40163,
                "category_id": 973,
                "val": "https:\/\/assets.beep.nl\/users\/1\/thumbs\/inspection\/jIcycTYnO8zYq6SHCvAwPHb97BDLFkZaDmfZUop5.png",
                "unit": null,
                "type": "image"
            }
        ]
    },
    {
        "id": 68477,
        "notes": null,
        "reminder": null,
        "reminder_date": null,
        "impression": 3,
        "attention": 1,
        "created_at": "2020-10-23 12:43:00",
        "checklist_id": 3206,
        "image_id": null,
        "owner": true,
        "thumb_url": null,
        "hive_id": 281,
        "items": []
    },
    {
        "id": 68478,
        "notes": "Hive change",
        "reminder": null,
        "reminder_date": null,
        "impression": null,
        "attention": null,
        "created_at": "2020-10-23 13:12:33",
        "checklist_id": null,
        "image_id": null,
        "owner": true,
        "thumb_url": null,
        "hive_id": 281,
        "items": [
            {
                "id": 522496,
                "value": "2",
                "inspection_id": 68478,
                "category_id": 85,
                "val": "2",
                "unit": null,
                "type": "number_positive"
            },
            {
                "id": 522497,
                "value": "2",
                "inspection_id": 68478,
                "category_id": 87,
                "val": "2",
                "unit": null,
                "type": "number"
            },
            {
                "id": 522498,
                "value": "10",
                "inspection_id": 68478,
                "category_id": 89,
                "val": "10",
                "unit": null,
                "type": "number_positive"
            }
        ]
    }
]
```
> Example response (401):

```json
{
    "message": "Unauthenticated."
}
```

### HTTP Request
`GET api/researchdata/{id}/user/{user_id}/{item}`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    date_start | datetime |  optional  | The date in 'YYYY-MM-DD HH:mm:ss' format (2020-01-01 00:00:00) to request data from (default is beginning of research, or earlier (except inspections and measurements).
    date_until | datetime |  optional  | The date in 'YYYY-MM-DD HH:mm:ss' format (2020-09-29 23:59:59) to request data until (default is until the end of the user consent, or research end).
    device_id | integer |  optional  | The device_id to filter the measurements on (next to date_start and date_until).
    precision | string |  optional  | Specifies the optional InfluxDB format/precision (rfc3339/h/m/s/ms/u) of the timestamp of the measurements and weather data: rfc3339 (YYYY-MM-DDTHH:MM:SS.nnnnnnnnnZ), h (hours), m (minutes), s (seconds), ms (milliseconds), u (microseconds). Precision defaults to rfc3339.

<!-- END_4f4cc2a64151945d3e57be605dc0b98a -->

#Api\SampleCodeController


Research lab result sample code controller
<!-- START_97f371a6334e98576ea879511226a683 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/samplecode" 
```

```javascript
const url = new URL("https://api.beep.nl/api/samplecode");

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
`GET api/samplecode`


<!-- END_97f371a6334e98576ea879511226a683 -->

<!-- START_a8bc3e7a0e51abfe3e43a713c5a2019f -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "https://api.beep.nl/api/samplecode" 
```

```javascript
const url = new URL("https://api.beep.nl/api/samplecode");

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
`POST api/samplecode`


<!-- END_a8bc3e7a0e51abfe3e43a713c5a2019f -->

<!-- START_ede7fea00e625e61218a3fe255960541 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/samplecode/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/samplecode/1");

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
`GET api/samplecode/{samplecode}`


<!-- END_ede7fea00e625e61218a3fe255960541 -->

<!-- START_051be30e038b6ec2beeaed77e8e1a73b -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "https://api.beep.nl/api/samplecode/1" 
```

```javascript
const url = new URL("https://api.beep.nl/api/samplecode/1");

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
`PUT api/samplecode/{samplecode}`

`PATCH api/samplecode/{samplecode}`


<!-- END_051be30e038b6ec2beeaed77e8e1a73b -->

<!-- START_828c58242f3e6d3d7f10e15ba93c914a -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "https://api.beep.nl/api/samplecode" 
```

```javascript
const url = new URL("https://api.beep.nl/api/samplecode");

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
`DELETE api/samplecode`


<!-- END_828c58242f3e6d3d7f10e15ba93c914a -->

#Api\SensorDefinitionController


<!-- START_3b98cb44d6fe4407585509ca2f891fda -->
## api/sensordefinition GET
Display a listing of all sensordefinitions that belong to a device

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/sensordefinition" \
    -H "Content-Type: application/json" \
    -d '{"device_id":10,"hardware_id":"ratione","device_hardware_id":"sit","input_measurement_abbreviation":"ratione","limit":8}'

```

```javascript
const url = new URL("https://api.beep.nl/api/sensordefinition");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "device_id": 10,
    "hardware_id": "ratione",
    "device_hardware_id": "sit",
    "input_measurement_abbreviation": "ratione",
    "limit": 8
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
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
`GET api/sensordefinition`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    device_id | integer |  optional  | Device ID that the Sensordefinition belongs to. Required if hardware_id, and device_hardware_id are not set.
    hardware_id | string |  optional  | Device hardware ID that the Sensordefinition belongs to. Required if device_id, and device_hardware_id are not set.
    device_hardware_id | string |  optional  | Device hardware ID that the Sensordefinition belongs to. Required if hardware_id, and device_id are not set.
    input_measurement_abbreviation | string |  optional  | Filter sensordefinitions by provided input abbreviation.
    limit | integer |  optional  | If input_abbr is set, limit the amount of results provided by more than 1 to get all historic sensordefinitions of this type.

<!-- END_3b98cb44d6fe4407585509ca2f891fda -->

<!-- START_f6f20cfc18b5c347368408d17f1981d8 -->
## api/sensordefinition POST
Store a newly created sensordefinition

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X POST "https://api.beep.nl/api/sensordefinition" \
    -H "Content-Type: application/json" \
    -d '{"name":"consequatur","inside":true,"offset":71018.38,"multiplier":19.674,"input_measurement_id":5,"input_measurement_abbreviation":"w_v","output_measurement_id":6,"output_measurement_abbreviation":"t_i","device_id":5,"hardware_id":"quos","device_hardware_id":"facere"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/sensordefinition");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "name": "consequatur",
    "inside": true,
    "offset": 71018.38,
    "multiplier": 19.674,
    "input_measurement_id": 5,
    "input_measurement_abbreviation": "w_v",
    "output_measurement_id": 6,
    "output_measurement_abbreviation": "t_i",
    "device_id": 5,
    "hardware_id": "quos",
    "device_hardware_id": "facere"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/sensordefinition`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    name | string |  optional  | Name of the sensorinstance (e.g. temperature frame 1)
    inside | boolean |  optional  | True is measured inside, false if measured outside
    offset | float |  optional  | Measurement value that defines 0
    multiplier | float |  optional  | Amount of units (calibration figure) per delta Measurement value to multiply withy (value - offset)
    input_measurement_id | integer |  optional  | Measurement that represents the input Measurement value (e.g. 5, 3).
    input_measurement_abbreviation | string |  optional  | Abbreviation of the Measurement that represents the input value (e.g. w_v, or t_i).
    output_measurement_id | integer |  optional  | Measurement that represents the output Measurement value (e.g. 6, 3).
    output_measurement_abbreviation | string |  optional  | Abbreviation of the Measurement that represents the output (calculated with (raw_value - offset) * multiplier) value (e.g. weight_kg, or t_i),
    device_id | integer |  optional  | Device ID that the Sensordefinition belongs to. Required if hardware_id, and device_hardware_id are not set.
    hardware_id | string |  optional  | Device hardware ID that the Sensordefinition belongs to. Required if device_id, and device_hardware_id are not set.
    device_hardware_id | string |  optional  | Device hardware ID that the Sensordefinition belongs to. Required if hardware_id, and device_id are not set.

<!-- END_f6f20cfc18b5c347368408d17f1981d8 -->

<!-- START_ff9674949ad011c894c60a5928baa7be -->
## api/sensordefinition/{id} GET
Display the specified sensordefinition

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/sensordefinition/1" \
    -H "Content-Type: application/json" \
    -d '{"device_id":1,"hardware_id":"totam","device_hardware_id":"et"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/sensordefinition/1");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "device_id": 1,
    "hardware_id": "totam",
    "device_hardware_id": "et"
}

fetch(url, {
    method: "GET",
    headers: headers,
    body: body
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
`GET api/sensordefinition/{sensordefinition}`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    device_id | integer |  optional  | Device ID that the Sensordefinition belongs to. Required if hardware_id, and device_hardware_id are not set.
    hardware_id | string |  optional  | Device hardware ID that the Sensordefinition belongs to. Required if device_id, and device_hardware_id are not set.
    device_hardware_id | string |  optional  | Device hardware ID that the Sensordefinition belongs to. Required if hardware_id, and device_id are not set.

<!-- END_ff9674949ad011c894c60a5928baa7be -->

<!-- START_4a67b5f3dbe74a8ad47cfe8979c6207b -->
## api/sensordefinition/{id} PATCH
Update the specified sensordefinition

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X PUT "https://api.beep.nl/api/sensordefinition/1" \
    -H "Content-Type: application/json" \
    -d '{"device_id":11,"hardware_id":"quos","device_hardware_id":"rerum"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/sensordefinition/1");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "device_id": 11,
    "hardware_id": "quos",
    "device_hardware_id": "rerum"
}

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PUT api/sensordefinition/{sensordefinition}`

`PATCH api/sensordefinition/{sensordefinition}`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    device_id | integer |  optional  | Device ID that the Sensordefinition belongs to. Required if hardware_id, and device_hardware_id are not set.
    hardware_id | string |  optional  | Device hardware ID that the Sensordefinition belongs to. Required if device_id, and device_hardware_id are not set.
    device_hardware_id | string |  optional  | Device hardware ID that the Sensordefinition belongs to. Required if hardware_id, and device_id are not set.

<!-- END_4a67b5f3dbe74a8ad47cfe8979c6207b -->

<!-- START_088c023a2cbee7a7aa87c3abaece68ea -->
## api/sensordefinition/{id} DELETE
Remove the specified sensordefinition

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X DELETE "https://api.beep.nl/api/sensordefinition/1" \
    -H "Content-Type: application/json" \
    -d '{"device_id":18,"hardware_id":"repellat","device_hardware_id":"quibusdam"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/sensordefinition/1");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "device_id": 18,
    "hardware_id": "repellat",
    "device_hardware_id": "quibusdam"
}

fetch(url, {
    method: "DELETE",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`DELETE api/sensordefinition/{sensordefinition}`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    device_id | integer |  optional  | Device ID that the Sensordefinition belongs to. Required if hardware_id, and device_hardware_id are not set.
    hardware_id | string |  optional  | Device hardware ID that the Sensordefinition belongs to. Required if device_id, and device_hardware_id are not set.
    device_hardware_id | string |  optional  | Device hardware ID that the Sensordefinition belongs to. Required if hardware_id, and device_id are not set.

<!-- END_088c023a2cbee7a7aa87c3abaece68ea -->

#Api\SettingController


<!-- START_1e1aaba3a713ac3ce04a89d5f4ad0f2e -->
## api/settings
> Example request:

```bash
curl -X POST "https://api.beep.nl/api/settings" 
```

```javascript
const url = new URL("https://api.beep.nl/api/settings");

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
curl -X GET -G "https://api.beep.nl/api/settings" 
```

```javascript
const url = new URL("https://api.beep.nl/api/settings");

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
`GET api/settings`


<!-- END_10633908636252dc838d3a5bd392f07c -->

#Api\TaxonomyController


<!-- START_f0dc9906634dd344e86ab96b3f489333 -->
## api/taxonomy/lists
List of current state of the standard categories.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/taxonomy/lists" 
```

```javascript
const url = new URL("https://api.beep.nl/api/taxonomy/lists");

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
`GET api/taxonomy/lists`


<!-- END_f0dc9906634dd344e86ab96b3f489333 -->

<!-- START_39819739c1fe97abb680ee9f8137ec84 -->
## api/taxonomy/taxonomy
List of current state of the standard categories, translated, unordered/ordered in hierachy/flat.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/taxonomy/taxonomy?locale=suscipit&flat=laudantium&order=est" 
```

```javascript
const url = new URL("https://api.beep.nl/api/taxonomy/taxonomy");

    let params = {
            "locale": "suscipit",
            "flat": "laudantium",
            "order": "est",
        };
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

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
`GET api/taxonomy/taxonomy`

#### Query Parameters

Parameter | Status | Description
--------- | ------- | ------- | -----------
    locale |  optional  | string Two character language code to translate taxonomy
    flat |  optional  | boolean In hierachy (default: true)
    order |  optional  | boolean Ordered (default: false)

<!-- END_39819739c1fe97abb680ee9f8137ec84 -->

#Api\UserController


APIs for managing users
<!-- START_d7b7952e7fdddc07c978c9bdaf757acf -->
## api/register
Registers a new user and sends an e-mail verification request on succesful save

> Example request:

```bash
curl -X POST "https://api.beep.nl/api/register" \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com","password":"testtest","password_confirmation":"testtest","policy_accepted":"beep_terms_2018_05_25_avg_v1"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/register");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "email": "test@test.com",
    "password": "testtest",
    "password_confirmation": "testtest",
    "policy_accepted": "beep_terms_2018_05_25_avg_v1"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/register`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    email | string |  required  | Email address of the user.
    password | string |  required  | Password of the user.
    password_confirmation | string |  required  | Password confirmation of the user.
    policy_accepted | string |  required  | Name of the privacy policy that has been accepted by the user by ticking the accept terms box.

<!-- END_d7b7952e7fdddc07c978c9bdaf757acf -->

<!-- START_c3fa189a6c95ca36ad6ac4791a873d23 -->
## api/login
Login via login form

> Example request:

```bash
curl -X POST "https://api.beep.nl/api/login" \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com","password":"testtest"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/login");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "email": "test@test.com",
    "password": "testtest"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "id": 1317,
    "name": "test@test.com",
    "email": "test@test.com",
    "avatar": "default.jpg",
    "api_token": "1snu2aRRiwQNl2Tul567hLF0XpKuZO8hqkgXU4GvjzZ3f3pOCiDPFbBDea7W",
    "created_at": "2018-12-30 23:57:35",
    "updated_at": "2020-01-09 16:31:32",
    "last_login": "2020-01-09 16:31:32",
    "policy_accepted": "beep_terms_2018_05_25_avg_v1",
    "email_verified_at": "2018-05-25 00:00:00"
}
```

### HTTP Request
`POST api/login`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    email | string |  required  | Email address of the user.
    password | string |  required  | Password of the user.

<!-- END_c3fa189a6c95ca36ad6ac4791a873d23 -->

<!-- START_ecb0fb5f68c755d234f5903658956ead -->
## api/user/reminder
Send password reset link
responses: invalid_user, reminder_sent, invalid_password, invalid_token, password_reset

> Example request:

```bash
curl -X POST "https://api.beep.nl/api/user/reminder" \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/user/reminder");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "email": "test@test.com"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```


> Example response (200):

```json
{
    "message": "reminder_sent"
}
```

### HTTP Request
`POST api/user/reminder`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    email | string |  required  | Email address of the user.

<!-- END_ecb0fb5f68c755d234f5903658956ead -->

<!-- START_5a5b59444cee7eb79d151113de4eec9c -->
## api/user/reset
Reset the user passowrd with a reset link
responses: INVALID_USER, RESET_LINK_SENT, INVALID_PASSWORD, INVALID_TOKEN, PASSWORD_RESET

> Example request:

```bash
curl -X POST "https://api.beep.nl/api/user/reset" \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com","password":"testtest","password_confirmation":"testtest","token":"z8iQafmgP1"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/user/reset");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "email": "test@test.com",
    "password": "testtest",
    "password_confirmation": "testtest",
    "token": "z8iQafmgP1"
}

fetch(url, {
    method: "POST",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`POST api/user/reset`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    email | string |  required  | Email address of the user.
    password | string |  required  | Password of the user.
    password_confirmation | string |  required  | Password confirmation of the user.
    token | string |  required  | Token sent in the reminder e-mail to the email address of the user.

<!-- END_5a5b59444cee7eb79d151113de4eec9c -->

<!-- START_4a6a89e9e0eaea9c72ceea57315f2c42 -->
## api/authenticate
Authorize a user and login with an api_token. Used for persistent login in webapp.

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
Header parameter with Bearer [api_token] from the user object. Example: Bearer 1snu2aRRiwQNl2Tul5F0XpKuZO8hqkgXU4GvjzZ3f3pOCiDPFbBDea7W

> Example request:

```bash
curl -X POST "https://api.beep.nl/api/authenticate" 
```

```javascript
const url = new URL("https://api.beep.nl/api/authenticate");

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


> Example response (200):

```json
{
    "id": 1317,
    "name": "test@test.com",
    "email": "test@test.com",
    "avatar": "default.jpg",
    "api_token": "1snu2aRRiwQNl2Tul567hLF0XpKuZO8hqkgXU4GvjzZ3f3pOCiDPFbBDea7W",
    "created_at": "2018-12-30 23:57:35",
    "updated_at": "2020-01-09 16:31:32",
    "last_login": "2020-01-09 16:31:32",
    "policy_accepted": "beep_terms_2018_05_25_avg_v1",
    "email_verified_at": "2018-05-25 00:00:00"
}
```

### HTTP Request
`POST api/authenticate`


<!-- END_4a6a89e9e0eaea9c72ceea57315f2c42 -->

<!-- START_43e8ba205ffd3cbca826e9ab0a6f5af1 -->
## api/user DELETE
Destroy the logged in user and all its data in the database

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X DELETE "https://api.beep.nl/api/user" 
```

```javascript
const url = new URL("https://api.beep.nl/api/user");

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
## api/user PATCH
Edit the user details, renew API token

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X PATCH "https://api.beep.nl/api/user" \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com","name":"Test","password":"testtest","password_new":"testtest1","password_confirmation":"testtest1","policy_accepted":"beep_terms_2018_05_25_avg_v1","locale":"en"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/user");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "email": "test@test.com",
    "name": "Test",
    "password": "testtest",
    "password_new": "testtest1",
    "password_confirmation": "testtest1",
    "policy_accepted": "beep_terms_2018_05_25_avg_v1",
    "locale": "en"
}

fetch(url, {
    method: "PATCH",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PATCH api/user`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    email | string |  required  | Email address of the user.
    name | string |  optional  | Name of the user.
    password | string |  required  | Password of the user with minimum of 8 characters.
    password_new | string |  optional  | New password to set for the user with minimum of 8 characters.
    password_confirmation | string |  optional  | Password confirmation of the user, required if password_new is filled.
    policy_accepted | string |  optional  | Name of the privacy policy that has been accepted by the user by ticking the accept terms box.
    locale | string |  optional  | Locale string to define locale.

<!-- END_e75f2f63a5a2351c4f4d83bc65cefcf8 -->

<!-- START_0b8bc7495acb6ee681c29e0334f9787c -->
## api/userlocale PATCH
Edit the user locale only, do not update api_key

<br><small style="padding: 1px 9px 2px;font-weight: bold;white-space: nowrap;color: #ffffff;-webkit-border-radius: 9px;-moz-border-radius: 9px;border-radius: 9px;background-color: #3a87ad;">Requires authentication</small>
> Example request:

```bash
curl -X PATCH "https://api.beep.nl/api/userlocale" \
    -H "Content-Type: application/json" \
    -d '{"locale":"vero"}'

```

```javascript
const url = new URL("https://api.beep.nl/api/userlocale");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
}

let body = {
    "locale": "vero"
}

fetch(url, {
    method: "PATCH",
    headers: headers,
    body: body
})
    .then(response => response.json())
    .then(json => console.log(json));
```



### HTTP Request
`PATCH api/userlocale`

#### Body Parameters

Parameter | Type | Status | Description
--------- | ------- | ------- | ------- | -----------
    locale | string |  optional  | Two digit country string to define locale

<!-- END_0b8bc7495acb6ee681c29e0334f9787c -->

#Api\WeatherController


Weather data request
<!-- START_f2ce4d0ca8bf3878a3fd61cbc4528bdd -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "https://api.beep.nl/api/weather" 
```

```javascript
const url = new URL("https://api.beep.nl/api/weather");

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
`GET api/weather`


<!-- END_f2ce4d0ca8bf3878a3fd61cbc4528bdd -->


