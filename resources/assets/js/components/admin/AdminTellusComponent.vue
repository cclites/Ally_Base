<template>
    <b-card>
        <h3>Tellus Testing</h3>

        <b-form-group label="Shift ID" label-for="shift_id">
            <b-input id="shift_id"
                     v-model="shift_id"
            />
            <b-btn @click="loadXml()" variant="primary">Load XML</b-btn>
            <b-btn :href="downloadUrl" variant="info">Download XML</b-btn>
        </b-form-group>

        <b-form-group label="XML" label-for="shift_id" v-if="loaded">
            <b-textarea :rows="10" v-model="xml"/>
            <b-btn @click="submit()" variant="success">Submit to Tellus</b-btn>
        </b-form-group>
    </b-card>
</template>

<script>
    export default {
        name: "AdminTellusComponent",
        data() {
            return {
                shift_id: '',
                xml: null,
                loaded: false,
            }
        },
        computed: {
            downloadUrl() {
                return '/admin/tellus/download/' + this.shift_id;
            }
        },
        methods: {
            async loadXml() {
                this.loaded = false;
                const response = await axios.get(this.downloadUrl);
                this.xml = response.data;
                this.loaded = true;
            },
            submit() {
                this.validateXML(this.xml);
                let form = new Form({xml: this.xml});
                alert('Submission coming soon');
            },
            validateXML(xml) {
                // code for IE
                if (window.ActiveXObject) {
                    var xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
                    xmlDoc.async = false;
                    xmlDoc.loadXML(xml);

                    if (xmlDoc.parseError.errorCode != 0) {
                        let txt = "Error Code: " + xmlDoc.parseError.errorCode + "\n";
                        txt = txt + "Error Reason: " + xmlDoc.parseError.reason;
                        txt = txt + "Error Line: " + xmlDoc.parseError.line;
                        alert(txt);
                        return false;
                    }
                    else {
                        alert("No errors found");
                    }
                }
                // code for Mozilla, Firefox, Opera, etc.
                else if (document.implementation.createDocument) {
                    var parser = new DOMParser();
                    var xmlDoc = parser.parseFromString(xml, "text/xml");

                    if (xmlDoc.getElementsByTagName("parsererror").length > 0) {
                        console.log(xmlDoc.getElementsByTagName("parsererror")[0]);
                        alert('XML Error Found');
                        return false;
                    }
                    else {
                        alert("No errors found");
                        return true;
                    }
                }
                else {
                    return confirm('Your browser cannot handle XML validation');
                }
            }
        }
    }
</script>

<style scoped>

</style>