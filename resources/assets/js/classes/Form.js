import AxiosResponseHandler from "./AxiosResponseHandler";

class Form {
    /**
     * Create a new Form instance.
     *
     * Passing in options can probably be extended to be a lot cooler.. maybe a deconstructed object.. let me know your thoughts
     * @param {object} data
     */
    constructor(data, alertOnSuccess = true ) {
        this.originalData = data;

        for (let field in data) {
            this[field] = data[field];
        }

        this.handler = new AxiosResponseHandler();
        this.resetOnSuccess = false;
        this.alertOnResponse = true;
        this.alertOnSuccess = alertOnSuccess;
        this.errorMods = 0;
        this.hideErrors = [];
        this.busy = false;
        this.hasBeenSubmitted = false;
    }

    disableRedirects() {
        this.handler.redirects = false;
        return this;
    }

    /**
     * Disable error messages for the provided HTTP status code
     */
    hideErrorsFor(statusCode) {
        this.hideErrors.push(statusCode);
        return this;
    }

    /**
     * Fetch all relevant data for the form.
     */
    data(multipart = false) {
        if (multipart) {
            let data = new FormData();

            for (let property in this.originalData) {
                data.append(property, this[property]);
            }
            return data;

        } else {
            let data = {};

            for (let property in this.originalData) {
                data[property] = this[property];
            }

            return data;
        }
    }

    /**
     * Check if the form, or form field, was modified (is dirty)
     *
     * @param field
     * @returns {boolean}
     */
    wasModified(field=null) {
        if (field) return (this[field] !== this.originalData[field]);
        for (let property in this.originalData) {
            if (this[property] !== this.originalData[property]) return true;
        }
        return false;
    }

    /**
     * Alias for wasModified
     *
     * @param field
     * @returns {boolean}
     */
    isDirty(field=null) {
        return this.wasModified(field);
    }

    /**
     * Reset the form fields.
     */
    reset(keepOriginalData = false) {
        for (let field in this.originalData) {
            if (keepOriginalData) {
                this[field] = this.originalData[field];
            } else {
                this[field] = '';
            }
        }

        this.clearError();
    }

    /**
     * Send a GET request to the given URL.  Converts data properties to the query string.
     * .
     * @param {string} url
     */
    get(url) {
        return this.submit('get', this.toQueryString(url));
    }

    /**
     * Send a POST request to the given URL.
     * .
     * @param {string} url
     */
    post(url) {
        return this.submit('post', url);
    }


    /**
     * Send a PUT request to the given URL.
     * .
     * @param {string} url
     */
    put(url) {
        return this.submit('put', url);
    }


    /**
     * Send a PATCH request to the given URL.
     * .
     * @param {string} url
     */
    patch(url) {
        return this.submit('patch', url);
    }


    /**
     * Send a DELETE request to the given URL.
     * .
     * @param {string} url
     */
    delete(url) {
        return this.submit('delete', url);
    }


    /**
     * Submit the form.
     *
     * @param {string} method
     * @param {string} url
     * @param multipart
     */
    submit(method, url, multipart = false) {
        this.busy = true;
        this.hasBeenSubmitted = true;
        const verb = method.toLowerCase();
        let Form = this;

        return new Promise((resolve, reject) => {
            axios[verb](url, this.data(multipart))
                .then(response => {
                    console.log('Axios success');
                    this.handler = new AxiosResponseHandler();
                    this.handler.handleResponse(response, this.alertOnResponse && this.alertOnSuccess);
                    if (this.resetOnSuccess) this.reset();
                    resolve(response);
                })
                .catch(error => {
                    console.log('Axios error');
                    this.handler = new AxiosResponseHandler();
                    this.handler.handleError(error, this.alertFromResponse, this.hideErrors);
                    reject(error);
                })
                .finally(() => {
                    this.busy = false;
                });
        });
    }

    /**
     * Check if an error exists for the field, or all fields if field is null
     *
     * @param field
     * @returns {*}
     */
    hasError(field = null) {
        if (this.handler) {
            return this.handler.hasFormError(field);
        }
        return false;
    }

    /**
     * Get the error message for the field, or the first available field if field is null
     *
     * @param field
     * @returns {*}
     */
    getError(field = null) {
        if (this.handler) {
            return this.handler.getFormError(field);
        }
        return null;
    }

    /**
     * Clear out errors for the field, or all fields if field is null
     *
     * @param field
     * @returns {*}
     */
    clearError(field = null) {
        if (this.handler) {
            this.handler.clearFormError(field);
            this.errorMods++;
        }
    }

    /**
     * Add an error to the field
     *
     * @param field
     * @param message
     * @returns {*}
     */
    addError(field, message) {
        if (this.handler) {
            this.handler.addFormError(field, message);
            this.errorMods++;
        }
    }

    /**
     * Fill the orignal form fields with the data of
     * the same name from the given object.
     */
    fill(newData, clearErrors = true) {
        for (let field in this.originalData) {
            this[field] = newData[field];
        }

        if (clearErrors) {
            this.clearError();
        }
    }

    /**
     * Combine the contents of another form into this
     * form so the data can be submitted together in one request
     */
    combineForm(otherForm) {
        for (let property in otherForm.originalData) {
            this.originalData[property] = otherForm.originalData[property];
            this[property] = otherForm[property];
        }
    }

    /**
     * Convert the form data into a query string for get requests.
     *
     * @param url
     * @returns {string|string}
     */
    toQueryString(url = '') {
        const data = this.data();
        for(let field in data) {
            let value = encodeURIComponent(data[field]);
            url += (url.includes('?')) ? `&${field}=${value}` : `?${field}=${value}`;
        }
        return url;
    }
}

export default Form;
