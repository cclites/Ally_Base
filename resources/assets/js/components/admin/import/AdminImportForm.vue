<template>
    <form @submit.prevent="startImport()" enctype="multipart/form-data" class="form-inline">
        <div class="form-group">
            <input type="file" name="document" @change="file = $event.target.files[0]">
        </div>

        <div class="form-group">
            <label for="provider">Import Type: </label>
            <select id="provider" v-model="provider" class="form-control">
                <option value="acorn">Acorn Format</option>
                <option value="sarasota">Sarasota Format</option>
            </select>
        </div>

        <div class="form-group">
            <label for="business_id">Business: </label>
            <select id="business_id" v-model="businessId" class="form-control">
                <option v-for="business in businesses" :value="business.id">{{ business.name }}</option>
            </select>
        </div>

        <button type="submit" class="btn btn-info" :disabled="submitting">
            <i class="fa fa-spinner fa-spin" v-show="submitting"></i> Start Import
        </button>
    </form>
</template>

<script>
    import FormDataForm from '../../../classes/FormDataForm';

    export default {
        props: {
            'businesses': Array,
        },

        data() {
            return {
                businessId: "",
                file: null,
                provider: "",
                submitting: false,
            }
        },

        mounted() {

        },

        methods: {
            async startImport() {
                this.submitting = true;
                let formData = new FormData();
                formData.append('file', this.file);
                formData.append('business_id', this.businessId);
                formData.append('provider', this.provider);
                let form = new FormDataForm(formData);
                form.setOptions({
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                try {
                    const response = await form.post('/admin/import');
                    this.$emit('imported', response.data);
                }
                catch(err) {
                    //
                }
                this.submitting = false;
            }
        },
    }
</script>
