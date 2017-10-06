import Form from "./Form";
import AxiosResponseHandler from "./AxiosResponseHandler";

class FormDataForm extends Form {
    /**
     * Create a new FormDataForm instance.
     *
     * @param {FormData} formData
     */
    constructor(formData) {
        let dataPairs = {};
        for (let pair of formData.entries()) {
            dataPairs[pair[0]] = pair[1];
        }
        super(dataPairs);
        this.formData = formData;
        this.options = {};
    }

    /**
     * Set options.
     *
     * @param {object} options
     */
    setOptions(options) {
        this.options = options;
    }


    /**
     * Submit the form.
     *
     * @param {string} method
     * @param {string} url
     */
    submit(method, url) {
        let Form = this;
        return new Promise((resolve, reject) => {
            axios[method](url, this.formData, this.options)
                .then(response => {
                    this.handler = new AxiosResponseHandler();
                    this.handler.handleResponse(response, this.alertOnResponse);
                    if (this.resetOnSuccess) this.reset();
                    resolve(response);
                })
                .catch(error => {
                    this.handler = new AxiosResponseHandler();
                    this.handler.handleError(error, this.alertFromResponse);
                    reject(error);
                });
        });
    }
}

export default FormDataForm;
