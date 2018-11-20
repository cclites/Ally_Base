# November 20, 2018

## Business Chains
 * There is now a concept above "businesses" called Business Chains.  This enables chains and franchises to manage multiple business locations.  
 * Caregivers and office users belong to chains, while almost all other resources still belong to businesses.
 * Caregivers can work for multiple chains and can work for any business inside the chain.
 * Office users can only work for one chain but can be restricted to only certain businesses inside the chain.
 
## Abstract Model Classes and New Interfaces
 * All models should now extend a base model controlled by us.  Either AuditableModel or BaseModel.   This allows us to more easily control global aspects of all models, such as query scopes, or auditing functionality in the future.
 * Most models should either belong to a business(es) or a business chain(s).  When they do, they should implement the BelongsToBusinessInterface or BelongsToChainInterface.
     - Traits exist to handle common functionality:  BelongsToBusinesses, BelongsToOneBusiness, BelongsToChains, BelongsToOneChain
     

## New Query Scopes
 * Almost all Office User (Business) Controller queries should now be using the forRequestedBusinesses() and ordered() query scopes.
     - forRequestedBusinesses() restricts results of models belonging to business(es) based on the businesses the authorized user has access to.  It also checks for an array input of 'businesses' from requests so the user can filter down on specific businesses they have access to.
     - ordered() is a new query scope that allows us to centralize control of default model ordering (defined in BaseModel). In the specific model, you can overwrite scopeOrdered() or fill in the $orderedColumn and $orderedDir properties.  $orderedDir will default to 'ASC'.  If $orderedColumn is empty, ordered() will not sort.
     - forAuthorizedChain() restricts results of models that belong to business chains.  An office user can only belong to one chain, so this doesn't accept any input.

 
## Similar scope on Reports
 * A new abstract report class, BusinessResourceReport, extends the same function (forRequestedBusinesses) to business reports.  You can also implement your own logic and adhere to the BusinessReportInterface.
 
 
## BusinessRequest abstract FormRequest class
 * The BusinessRequest class helps us standardize requests that require a business_id. This performs the following tasks for us:
    - Automatically inserts the business_id for single-business office users.
    - Checks the provided business_id to make sure the office user has access to that business.
    - Gives us access to the selected business through `$request->getBusiness()` or the id at `$request->getBusinessId()`
    - Defines the authorize() method as true.  Put authorization logic in the controller.
 * Because of this helper class, it makes it more reasonable to move most office user request logic out of controllers.
 
 
## Authorization through Policies
 * Policies should be used to determine authorized access to models.  The standard methods should be: create, read, update, and delete.  All policies should extend the abstract BasePolicy class and should be registered in the AuthServiceProvider.
     - Policies can be checked in the controller using the `$this->authorize('ability', $model)`
     - For creations, you can follow the other policy classes and pass the $data with the model name: `$this->authorize('create', [Model::class, $data])`
 * Look at existing policies for examples.  The businessCheck and businessChainCheck methods can be commonly used.
 
 
## New Business Store in Vuex
 * Office users and admins now have a store that is injected with all of the businesses they have access to.  This store is managed by Vuex and injected into the Vue app.
    - The injection occurs in the scripts.blade.php partial.
    - An example usage of the state and getter can be seen in BusinessLocationSelect.vue.
    - Vuex documentation at https://vuex.vuejs.org/.  Use map functions in computed properties where applicable.
 * New modules can be added in the resources/js/store/modules directory and registered in store/index.js file.