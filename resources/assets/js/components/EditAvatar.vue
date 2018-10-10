<template>
    <div class="d-block">
        <div v-if="value && ! isEditing" id="avatar-box" class="avatar mb-2">
            <img :src="value" />
        </div>
        <div id="cropbox" class="hidden"></div>

        <div v-if="isEditing">
            <b-btn @click="crop" variant="success">Crop</b-btn>
            <b-btn @click="cancel" variant="secondary">Cancel</b-btn>
        </div>
        <div v-else>
            <b-btn @click="selectFile" variant="success">Upload a Photo</b-btn>
            <b-btn v-if="value" @click="clear" variant="secondary">Clear</b-btn>
        </div>
        <input ref="file" type="file" id="upload" class="hidden" value="Choose a file" accept="image/*" />
    </div>
</template>

<script>
    export default {
        props: {
            value: {
                type: String,
                default: '',
            },
            size: {
                type: Number,
                default: 150,
            },
            cropperPadding: {
                type: Number,
                default: 100,
            },
        },

        data: () => ({
            cropBox: null,
            isEditing: false,
        }),

        methods: {
            selectFile() {
                this.$refs.file.click();
            },

            readFile(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    
                    reader.onload = (e) => {
                        $('.upload-demo').addClass('ready');
                        this.cropBox.croppie('bind', {
                            url: e.target.result,
                        }).then(() => {
                            this.isEditing = true;
                            this.cropBox.show();
                            this.cropBox.croppie('bind');
                        })
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                }
                else {
                    console.log("Sorry - you're browser doesn't support the FileReader API");
                }
            },

            crop() {
                this.cropBox.croppie('result', {
                    type: 'canvas',
                    size: 'viewport',
                }).then(resp => {
                    this.isEditing = false;
                    this.$emit('input', resp);
                    this.cropBox.hide();
                    $('#upload').val('');
                });
            },

            clear() {
                this.$emit('input', '/images/default-avatar.png');
                $('#upload').val('');
            },

            cancel() {
                this.isEditing = false;
                this.cropBox.hide();
            },
        },

        mounted() {
            this.cropBox = $('#cropbox').croppie({
                enableExif: true,
                viewport: {
                    width: parseInt(this.size),
                    height: parseInt(this.size),
                    type: 'square'
                },
                boundary: {
                    width: parseInt(this.size) + parseInt(this.cropperPadding),
                    height: parseInt(this.size) + parseInt(this.cropperPadding),
                }
            });

            let component = this;
            $('#upload').on('change', function () { component.readFile(this); });
        },
    }
</script>

<style scoped>
.hidden { display: none }

</style>