class AxiosResponseHandler {

    constructor() {
        this.error = {};
        this.formErrors = {};
        this.response = {};
        this.redirects = true;
    }

    handleResponse(response, alert = true) {

        this.response = response;
        if (alert && this.getMessage()) this.handleAlert('success', this.getMessage());
        if (this.hasRedirect()) this.handleRedirect();
    }

    handleError(error, alert = true, ignoreStatuses = []) {
        this.error = error;
        this.response = error.response;
        if (ignoreStatuses.includes(this.response.status)) {
            return;
        }
        if (this.response.status === 503) {
            this.handleAlert('error', 'The application is updating. Please try again in 1 minute.');
            return
        }
        if (this.response.data.errors) {
            this.formErrors = this.response.data.errors;
        }
        if (alert) {
            if (this.hasFormError()) {
                this.handleAlert('error', this.getFormError());
            }
            else if (this.getMessage()) {
                this.handleAlert('error', this.getMessage());
            }
            else {
                this.handleAlert('error', 'Please refresh and try again.');
            }
        }
        if (this.hasRedirect()) this.handleRedirect();
    }

    handleAlert(type, message) {
        if (this.hasRedirect()) {
            alerts.flashMessage(type, message);
            return;
        }
        alerts.addMessage(type, message);
    }

    getStatusCode() {
        return (this.response && this.response.status) ? this.response.status : 0;
    }

    getResponseData() {
        return (this.response && this.response.data) ? this.response.data : null;
    }

    getMessage() {
        let data = this.getResponseData();
        return (data.message) ? data.message : null;
    }

    hasRedirect() {
        if (!this.redirects) {
            console.log('redirects disabled');
            return false;
        }

        let data = this.getResponseData();
        return data.hasOwnProperty('redirect');
    }

    handleRedirect() {
        let data = this.getResponseData();
        let current = window.location.pathname + window.location.search + window.location.hash;
        if (data.redirect === '.' || data.redirect === current) {
            window.location.reload();
            return;
        }
        window.location = data.redirect;
    }

    hasError() {
        return Object.keys(this.error) > 0;
    }

    hasFormError(field = null) {
        if (!field) {
            return Object.keys(this.formErrors).length > 0;
        }
        return this.formErrors.hasOwnProperty(field);
    }

    getFormError(field = null) {
        if (!this.hasFormError(field)) {
            return null;
        }
        if (!field) {
            field = Object.keys(this.formErrors)[0];
        }
        let formError = this.formErrors[field];
        if (Array.isArray(formError)) {
            if (formError.length === 0) {
                return 'Unknown error when processing ' + field;
            }
            return formError[0];
        }
        return formError;
    }

    addFormError(field, message) {
        if (!this.hasFormError(field)) {
            this.formErrors[field] = [];
        }
        this.formErrors[field].push(message);
    }

    clearFormError(field = null) {
        if (!field) {
            this.formErrors = {};
            return;
        }

        if (Vue) {
            return Vue.delete(this.formErrors, field);
        }
        return delete this.formErrors[field];
    }

}

export default AxiosResponseHandler;
