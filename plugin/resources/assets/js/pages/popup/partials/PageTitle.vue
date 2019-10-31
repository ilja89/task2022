<template>
  <h1
    class="title is-3 page-title bottom-border-separator"
    :class="{ 'title--with-right': $slots.default }"
  >
    <template v-if="hasRight">
      <div>{{ currentTitle }}</div>
      <div v-if="this.student" class="student-groups">
        <div v-if="groupsDirect.length > 0">
          <div v-if="groupsDirect.length > 2">
            <tippy to="userGroups" arrow>
              <div>
                <h3>Groups for {{this.student.firstname}} {{this.student.lastname}}</h3>
                <p v-for="group in groupsDirect" v-bind:key="group.name">{{group.name}}</p>
              </div>
            </tippy>

            <div class="chip-button" name="userGroups">
              <h3>
                Groups
                <span class="badge badge--smaller badge--info">{{groupsDirect.length}}</span>
              </h3>
            </div>
          </div>
          <div v-else>
            <tippy to="groupMembers" arrow>
              <div>
                <h3>Groups for {{this.student.firstname}} {{this.student.lastname}}</h3>
                <p
                  v-for="member in groupsDirect[0].members"
                  v-bind:key="member.idnumber"
                >{{member}}}</p>
              </div>
            </tippy>
            <div
              v-for="group in groupsDirect"
              v-bind:key="group.name"
              name="groupMembers"
              class="chip"
            >{{group.name}}</div>
          </div>
        </div>
      </div>
      <div>
        <slot></slot>
      </div>
    </template>
    <template v-else>
      <div>
        {{ currentTitle }}
        <div v-if="this.student" class="column student-groups">
          <div v-if="groupsDirect.length > 0">
            <div v-for="group in groupsDirect" v-bind:key="group.name">
              <div :name="createBadgeName(group.id)" class="chip">{{group.name}}</div>
              <tippy :to="createBadgeName(group.id)" arrow>
                <div>
                  <h3>Users in this group</h3>
                  <p
                    v-for="member in group.members"
                    v-bind:key="member.idnumber"
                  >{{member.firstname}} {{member.lastname}} ({{member.idnumber}})</p>
                </div>
              </tippy>
            </div>
          </div>
        </div>
      </div>
    </template>
  </h1>
</template>

<script>
import { mapState, mapGetters } from "vuex";
import VueTippy, { TippyComponent } from "vue-tippy";
export default {
  components: { TippyComponent },
  props: {
    title: {
      required: false,
      default: ""
    }
  },

  computed: {
    ...mapState(["student"]),
    hasRight() {
      return !!this.$slots.default;
    },

    currentTitle() {
      if (this.student !== null) {
        return `${this.student.firstname} ${this.student.lastname}`;
      } else {
        return this.title;
      }
    },
    groupsDirect() {
      if (this.student !== null) {
        return this.student.groups;
      } else {
        return [];
      }
    }
  },
  methods: {
    createBadgeName(groupId) {
      return "group_badge_" + groupId;
    }
  }
};
</script>

<style lang="scss" scoped>
.title--with-right {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
</style>
