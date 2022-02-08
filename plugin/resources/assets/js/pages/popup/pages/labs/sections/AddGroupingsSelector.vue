<template>
  <div>

    Add grouping
    <div>
      <v-autocomplete v-model="grouping"
                      :items="courseGroupings"
                      item-text="name"
                      item-value="id"
                      label="Select grouping"
                      @change="groupingSelected"
                      return-object>
      </v-autocomplete>
    </div>

    <div>
	        <span class="multiselect__tag" v-for="g in groupingsSorted" :key="g.id">
	            <span> {{ g.name }} </span>
	            <i class="multiselect__tag-icon" @mousedown.prevent="removeGrouping(g)"></i>
	        </span>
    </div>

  </div>
</template>

<script>

export default {

  data() {
    return {
      grouping: {},
    }
  },

  props: {
    lab: {required: true},
    courseGroupings: {required: true},
  },

  methods: {

    groupingSelected() {
      var self = this;
      if (!self.lab.groupings.find( (g) => { return g.id == this.grouping.id; } )) {
        self.lab.groupings.push(this.grouping);
      }

      this.group = {};
    },

    removeGrouping(g) {
      let indx = this.lab.groupings.indexOf(g);
      this.lab.groupings.splice(indx, 1);
    },
  },

  computed: {
    groupingsSorted: function() {
      return this.lab.groupings.sort((a,b) => { return a.name.localeCompare(b.name); });
    },
  },
}
</script>


<style>
.grp-btn-active {
  color: green !important;
}
</style>