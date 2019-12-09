<template>
    <div>
        <button @click="draw()" type="button" 
                class="btn" :class="signature.length ? 'btn-info' : 'btn-outline-info'"
                :disabled="signature.length > 0">
            <span v-if="signature.length">
                <i class="fa fa-check"></i>&nbsp;&nbsp;&nbsp;Signed
            </span>
            <span v-else>{{ buttonTitle }}</span>
        </button>
        
        <input type="hidden" name="signature" v-model="signature" />
        <img :src="preview" class="img-responsive" v-if="preview" />
        
        <div class="signature-pad" v-show="signing">
            <canvas :id="uid" class="canvas" :data-uid="uid"></canvas>
            <div class="dotted-line"></div>
            <div class="actions">
                <button @click="signing = false" type="button" class="btn btn-danger">
                    Cancel
                </button>
                <button @click="clear()" type="button" class="btn btn-secondary">
                    Clear Signature
                </button>
                <button @click="save()" type="button" class="btn btn-success">
                    Done Signing
                </button>
            </div>
            <div class="landscape-warning" v-show="landscapeMode == false">
                Turn your phone sideways (landscape mode) to add a signature
                <i class="fa fa-refresh fa-lg"></i>
                <!--<i class="fa fa-rotate-left fa-lg"></i>-->
                <!--
                    <button @click="signing = false" type="button" class="btn btn-danger btn-lg">
                        No Thanks
                    </button>
                -->
            </div>
        </div>
    </div>
</template>

<script lang=babel>
    import SignaturePad from 'signature_pad'
    export default {
        name:"signaturePad",
        props: {
            sigOption: {
                type:Object,
                default:()=>{penColor : 'rgb(0, 0, 0)'},
            },
            w:{
                type:String,
                default:"100%"
            },
            h:{
                type:String,
                default:"100%"
            },
            buttonTitle:{
                type:String,
                default:'Add a Signature'
            }
        },
        data:() => ({
            sig: () => {},
            uid: "",
            signing: false,
            canvas: null,
            preview: "",
            signature: "",
            landscapeMode: ""
        }),
        created() {
            this.uid = "canvas" + this._uid
        },
        methods: {
            setLandscapeMode() {
                if (window.innerHeight > window.innerWidth) {
                    return this.landscapeMode = false;
                }
                //alert("You are now in landscape");
                return this.landscapeMode = true;
            },
            draw() {
                this.signing = true;
                this.$nextTick(() => {
                    this.canvas = document.getElementById(this.uid);
                    this.sig = new SignaturePad(this.canvas, this.sigOption);                
                    window.addEventListener("resize", this.resizeCanvas);
                    this.resizeCanvas();
                });
            },
            resizeCanvas() {
                this.setLandscapeMode();
                
                if (this.sig.isEmpty()) {
                    var ratio =  Math.max(window.devicePixelRatio || 1, 1);
                    console.log(ratio)
                    this.canvas.width = this.canvas.offsetWidth * ratio;
                    this.canvas.height = this.canvas.offsetHeight * ratio;
                    this.canvas.getContext("2d").scale(ratio, ratio);
                }
            },
            clear() {
                this.sig.clear();
            },
            save(format = "image/svg+xml") {
                if (this.sig.isEmpty()) {
                    return;
                }
                var dataUrl = format ? this.sig.toDataURL(format) :  this.sig.toDataURL()                
                var blob = atob(dataUrl.split(',')[1])
                //console.log(blob);
                this.signature = blob;
                this.signing = false;
                this.preview = this.sig.toDataURL()
                this.$emit('input', this.signature)
                /*
                    signaturePad.toDataURL(); // save image as PNG
                    signaturePad.toDataURL("image/jpeg"); // save image as JPEG
                    signaturePad.toDataURL("image/svg+xml"); // save image as SVG
                */
            }
        }
    }
</script>

<style>
    .signature-pad {
        position: fixed;
        height: 100%;
        width: 100%;
        top: 0;
        left: 0;
        background: #eeeeee;
        z-index: 999;
        text-align: center;
    }
    .fa-lg {
        font-size:80px; 
        display:block;
        padding: 40px 0;
    }
    .landscape-warning {
        visibility: hidden;
        background: #1f88e5;
        height: 100%;
        width: 100%;
        position: absolute;
        top: 0;
        font-size: 2rem;
        padding: 50px;
        color: white;
    }    
    .dotted-line {
        border-bottom: 2px dashed #dddddd;
        max-width: 650px;
        position: relative;
        margin: auto;
        margin-top: -80px;
        margin-bottom: 80px;
    }
    canvas {
        width: 100%;
        height: 80%;
        max-width: 800px;
        max-height: 300px;
        background: white;
        margin: auto;
        z-index: 9999;
    }
    .actions {
        position: relative;
        bottom: 35px;
    }
    @media screen and (max-width: 600px) {    
        .landscape-warning {
            visibility: visible;
        }
    }
</style>