# November 15, 2018

* All models should now extend a base model controlled by us.  Either AuditableModel or BaseModel.   This allows us to more easily control global aspects of all models, such as query scopes, or auditing functionality in the future.
* Almost all Office User (Business) Controller queries should now be using the forAuthorizedBusinesses() and ordered() query scopes.
    - forAuthorizedBusinesses() restricts results of models belonging to business(es) based on the businesses the authorized user has access to.  It also allows the an array input of 'businesses' from requests so the user can filter down on specific businesses.
    - ordered() is a new query scope that allows us to centralize control of default model ordering. In the model, you can overwrite scopeOrdered or fill in $orderedColumn and $orderedDir properties.
 