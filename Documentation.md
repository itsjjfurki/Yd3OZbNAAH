### ConstructionStages

---

`GET` `/constructionStages`

Lists all construction stages

---

`GET` `/constructionStages/{id}`

Gets single construction stage

---

`POST` `/constructionStages`

Creates new construction stage

##### Parameters

`name` `string` Maximum of 255 characters

`start_date` `string|Datetime` is a valid date&time in iso8601 format i.e. 2022-12-31T14:59:00Z

`end_date` `string|Datetime|null` is either null or a valid datetime which is later than the `start_date`

`durationUnit` `string` is one of HOURS, DAYS, WEEKS or can be skipped (which fallbacks to default value of DAYS)

`color` `string|null` is either null or a valid HEX color i.e. #FF0000

`externalId` `string|null` is null or any string up to 255 characters in length

`status` `string` is one of NEW, PLANNED or DELETED and the default value is NEW.


---

`PATCH` `/constructionStages/{id}`

Updates existing construction stage, rewrites only the fields which are sent by the user

##### Parameters

`name` `string` Maximum of 255 characters

`start_date` `string|Datetime` is a valid date&time in iso8601 format i.e. 2022-12-31T14:59:00Z

`end_date` `string|Datetime|null` is either null or a valid datetime which is later than the `start_date`

`durationUnit` `string` is one of HOURS, DAYS, WEEKS or can be skipped (which fallbacks to default value of DAYS)

`color` `string|null` is either null or a valid HEX color i.e. #FF0000

`externalId` `string|null` is null or any string up to 255 characters in length

`status` `string` is one of NEW, PLANNED or DELETED and the default value is NEW.


---

`DELETE` `/constructionStages/{id}`

Deletes construction stage

---

