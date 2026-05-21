<template>
  <v-sheet
    id="pricing"
    class="d-flex align-center py-16"
    color="secondary"
  >
    <v-container>
      <v-row class="d-flex align-center">
        <v-col cols="12" md="5">
          <div class="text-h4 font-weight-bold">
            Great pricing plans tailored to your needs.
          </div>

          <div class="text-body-1 text-medium-emphasis mt-5">
            Quis autem velis ets reprehender net etid quiste voluptate. Utise wisi
            enim minim veniam, quis et stationes nets.
          </div>
        </v-col>

        <v-col cols="12" md="3">
          <v-card
            class="pt-10 pb-5 d-flex flex-column align-center justify-center ga-5"
            elevation="5"
          >
            <div class="text-h6 font-weight-bold">
              MONTHLY
            </div>

            <div class="text-h2 font-weight-bold">
              $19
            </div>

            <div class="text-body-1 text-medium-emphasis">
              Per Month
            </div>

            <v-btn
              class="text-body-1 font-weight-bold"
              color="surface-variant"
              flat
              rounded
              text="GET STARTED"
            />

            <div class="text-center text-body-1 text-medium-emphasis">
              First two weeks free. <br>
              Amazing features.
            </div>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card
            class="pt-10 pb-5 d-flex flex-column align-center justify-center ga-10"
            color="primary"
            elevation="5"
          >
            <div class="text-h6 font-weight-bold">
              ANNUALY
            </div>

            <div class="text-h2 font-weight-bold">
              $29
            </div>

            <div class="text-body-1 text-white">
              Per Month
            </div>

            <v-btn
              class="px-10 text-body-1"
              color="accent"
              flat
              height="55"
              rounded
              text="GET IN TOUCH"
            />

            <div class="text-center text-body-1">
              First two weeks free. <br>
              Save 45% with this plan<br>Amazing features.
            </div>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </v-sheet>
</template>
