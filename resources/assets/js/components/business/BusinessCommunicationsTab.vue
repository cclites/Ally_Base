<template>
    <div>
            <b-card header="Text message auto reply settings"
                    header-text-variant="white"
                    header-bg-variant="info"
                    class="pb-3"
            >

                <form @submit="saveMessaging" name="messaging">

                  <div class="row auto_reply_checkboxes">

                      <div class="col-lg-6">
                          <b-form-group label="Auto reply off" label-for="auto_off">
                              <b-form-checkbox
                                      id="auto_off"
                                      name="auto_off"
                                      v-model="form.auto_off"
                              >
                              </b-form-checkbox>
                          </b-form-group>
                          <b-form-group label="Auto reply on indefinitely" label-for="on_indefinitely">
                              <b-form-checkbox
                                      id="on_indefinitely"
                                      name="on_indefinitely"
                                      v-model="form.on_indefinitely"
                              >
                              </b-form-checkbox>
                          </b-form-group>
                      </div>

                      <div class="col-lg-6">
                          <b-form-group label="Auto Reply Message" for="message" label-class="required">
                              <b-form-textarea
                                      id="message"
                                      name="message"
                                      v-model="form.message"
                                      placeholder="Enter auto respond message"
                                      rows="3"
                                      max-rows="6"
                                      required
                              ></b-form-textarea>

                          </b-form-group>
                      </div>

                    </div>

                    <div class="col-md-4">

                        <h5>Week days (Monday-Friday) hours that auto reply is active:</h5>


                        <b-form-group label="Start Time" label-for="week-start-time" label-class="required">
                            <time-picker id="week-start-time" v-model="form.week_start"></time-picker>
                        </b-form-group>

                        <b-form-group label="End Time" label-for="week-end-time" label-class="required">
                            <time-picker id="week-end-time" v-model="form.week_end"></time-picker>
                        </b-form-group>

                        <br>
                        <h5>Weekend days (Saturday &amp; Sunday) hours that auto reply is active:</h5>


                        <b-form-group label="Start Time" label-for="weekend-start-time" label-class="required">
                            <time-picker id="weekend-start-time" v-model="form.weekend_start"></time-picker>
                        </b-form-group>

                        <b-form-group label="End Time" label-for="weekend-end-time" label-class="required">
                            <time-picker id="weekend-end-time" v-model="form.weekend_end"></time-picker>
                        </b-form-group>

                    </div>

                    <div class="text-right"><button id="save_auto_response_configs" type="button" class="btn btn-success" @click="saveMessaging()">Save Messaging Options</button></div>

                </form>

            </b-card>
    </div>
</template>

<script>
    import Constants from '../../mixins/Constants';

    export default {

        async mounted() {
            await this.fetchMessagingData();
        },

        mixins : [Constants],

        data() {
            return {
                form: new Form({
                    auto_off: '',
                    on_indefinitely: '',
                    week_start: '',
                    week_end: '',
                    weekend_start: '',
                    weekend_end: '',
                    message: '',
                }),
            }
        },

        methods: {


            async fetchMessagingData(){
                let response = await axios.get('/business/communication/sms-autoresponse/' + this.businessId)
                            .then(response => {

                                let data = response.data;
                                this.form.auto_off = data.auto_off;
                                this.form.on_indefinitely = data.on_indefinitely;
                                this.form.message = data.message;
                                this.form.week_start = this.formatMysqlTime(data.week_start);
                                this.form.week_end = this.formatMysqlTime(data.week_end);
                                this.form.weekend_start = this.formatMysqlTime(data.weekend_start);
                                this.form.weekend_end = this.formatMysqlTime(data.weekend_end);

                            }).catch(error => {
                            console.log("Logging error response");
                                console.error(error.response);
                            });
            },

            saveMessaging(){
                let params = '?auto_off=' + this.form.auto_off + "&on_indefinitely=" +
                             this.form.on_indefinitely + "&message=" + this.form.message +
                             '&week_start=' + this.form.week_start + '&week_end=' + this.form.week_end +
                             '&weekend_start=' + this.form.weekend_start + '&weekend_end=' + this.form.weekend_end;

                const response = axios.post('/business/communication/sms-autoresponse/' + this.businessId + params)
                                        .then(response => {
                                            console.log(response);
                                        }).catch(error => {
                                            console.error(error.response);
                                        });
            },

            formatMysqlTime(time){
                return time.slice(0,5);
            }
        },



        name: "ClientCommunicationsTab",

        props: {
            businessId : Number,
        },

        watch: {
            message: function(val){
                if(val.length >= smsLength){
                   //this.form.message = val.substring(0, smsLength);
                }
            },
            week_start: function(val){
                this.form.week_start = val;
            },
            week_end: function(val){
                this.form.week_end = val;
            },
            weekend_start: function(val){
                this.form.weekend_start = val;
            },
            weekend_end: function(val){
                this.form.weekend_end = val;
            },
        }
    }
</script>

<style scoped>

</style>