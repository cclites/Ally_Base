export default class Languages {
    constructor()
    {
        this.languages = [
            {
                name: "English",
                abbreviation: "en"
            },
            {
                name: "Spanish",
                abbreviation: "es"
            },
            {
                name: "French",
                abbreviation: "fr"
            },
            {
                name: "German",
                abbreviation: "de"
            }
        ];
    }

    getOptions() {
        return this.languages.map(function(state) {
            return {
                // [state.name]: state.code
                value: state.abbreviation,
                // label: state.name
                text: state.name
            };
        })
    }
}
