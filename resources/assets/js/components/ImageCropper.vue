<template>
    <div class="d-block">
        <div v-if="value && ! isEditing" :class="{
            avatar: true,
            'mb-2': true,
            circle: circle,
        }" :style="{ width: width+'px', height: height+'px' }">
            <img :src="value" />
        </div>
        <div id="cropbox" class="hidden"></div>

        <div v-if="isEditing">
            <b-btn @click="crop()" variant="success">Crop</b-btn>
            <b-btn @click="cancel()" variant="secondary">Cancel</b-btn>
        </div>
        <div v-else>
            <b-btn @click="selectFile" variant="success">Upload a Photo</b-btn>
            <b-btn v-if="value" @click="clear()" variant="secondary">Clear</b-btn>
        </div>
        <input ref="file" type="file" id="upload" class="hidden" value="Choose a file" accept="image/*" />
    </div>
</template>

<script>
    import Constants from "../mixins/Constants";

    export default {
        mixins: [ Constants ],
        props: {
            defaultImage: {
                type: String,
                default: '',
            },
            value: {
                type: String,
                default: '',
            },
            height: {
                default: 150,
            },
            width: {
                default: 150,
            },
            cropperPadding: {
                default: 100,
            },
            circle: {
                type: Boolean,
                default: false,
            }
        },

        data() {
            return {
                cropBox: null,
                isEditing: false,
            }
        },

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
                this.$emit('input', this.defaultImage ? this.defaultImage : this.defaultAvatarUrl);
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
                    width: parseInt(this.width),
                    height: parseInt(this.height),
                    type: 'square'
                },
                boundary: {
                    width: parseInt(this.width) + parseInt(this.cropperPadding),
                    height: parseInt(this.height) + parseInt(this.cropperPadding),
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