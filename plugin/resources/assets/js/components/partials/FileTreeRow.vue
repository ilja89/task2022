<template>
  <li class="file-tree-row" :class="containerClass">

    <div class="file-tree-row__title-row"
         :style="'padding-left: ' + leftPadding + 'px;'"
         v-if="isDirectory"
         @click="toggleOpen">

      <div class="file-tree-row__title-item file-tree-row__arrow">
        <svg xmlns="http://www.w3.org/2000/svg"
             x="0px" y="0px" viewBox="0 0 451.846 451.847"
             style="enable-background:new 0 0 451.846 451.847;" xml:space="preserve">
          <g>
            <path d="M345.441,248.292L151.154,442.573c-12.359,12.365-32.397,12.365-44.75,0c-12.354-12.354-12.354-32.391,0-44.744
                L278.318,225.92L106.409,54.017c-12.354-12.359-12.354-32.394,0-44.748c12.354-12.359,32.391-12.359,44.75,0l194.287,194.284
                c6.177,6.18,9.262,14.271,9.262,22.366C354.708,234.018,351.617,242.115,345.441,248.292z"/>
          </g>
        </svg>
      </div>

      <div class="file-tree-row__title-item file-tree-row__icon">
        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
             viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve">
          <g>
            <path d="M56.981,11.5H28.019V6.52c0-1.665-1.354-3.02-3.019-3.02H3.019C1.354,3.5,0,4.854,0,6.52V20.5h60v-5.98
                  C60,12.854,58.646,11.5,56.981,11.5z"/>
            <path d="M0,53.48c0,1.665,1.354,3.02,3.019,3.02h53.962c1.665,0,3.019-1.354,3.019-3.02V22.5H0V53.48z"/>
          </g>
        </svg>
      </div>

      <div class="file-tree-row__title-item file-tree-row__title">
        {{data.title}}
      </div>

    </div>

    <div class="file-tree-row__title-row"
         :style="'padding-left: ' + leftPadding + 'px;'"
         v-else
         @click="$emit('file-clicked', data)">

      <div class="file-tree-row__title-item file-tree-row__icon">
        <svg viewBox="0 0 48 60" version="1.1" xmlns="http://www.w3.org/2000/svg">
          <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
            <g class="fillable" fill-rule="nonzero">
              <path
                d="M47.5,31.292 L47.5,14.586 L32.914,0 L0.5,0 L0.5,60 L47.5,60 L47.5,31.292 Z M33.5,4 L43.5,14 L33.5,14 L33.5,4 Z M11.5,14 L21.5,14 C22.052,14 22.5,14.447 22.5,15 C22.5,15.553 22.052,16 21.5,16 L11.5,16 C10.948,16 10.5,15.553 10.5,15 C10.5,14.447 10.948,14 11.5,14 Z M11.5,22 L36.5,22 C37.052,22 37.5,22.447 37.5,23 C37.5,23.553 37.052,24 36.5,24 L11.5,24 C10.948,24 10.5,23.553 10.5,23 C10.5,22.447 10.948,22 11.5,22 Z M11.5,30 L36.5,30 C37.052,30 37.5,30.447 37.5,31 C37.5,31.553 37.052,32 36.5,32 L11.5,32 C10.948,32 10.5,31.553 10.5,31 C10.5,30.447 10.948,30 11.5,30 Z M11.5,38 L36.5,38 C37.052,38 37.5,38.447 37.5,39 C37.5,39.553 37.052,40 36.5,40 L11.5,40 C10.948,40 10.5,39.553 10.5,39 C10.5,38.447 10.948,38 11.5,38 Z M11.5,46 L36.5,46 C37.052,46 37.5,46.447 37.5,47 C37.5,47.553 37.052,48 36.5,48 L11.5,48 C10.948,48 10.5,47.553 10.5,47 C10.5,46.447 10.948,46 11.5,46 Z"></path>
            </g>
          </g>
        </svg>
      </div>

      <div class="file-tree-row__title-item file-tree-row__title">
        {{ data.title }}
      </div>

    </div>

    <ul v-show="is_open" v-if="isDirectory" ref="directory-content">
      <file-tree-row
        v-for="(data, index) in data.contents"
        :key="index"
        :data="data"
        :level="level + 1"
        @file-clicked="$emit('file-clicked', $event)">
      </file-tree-row>
    </ul>

  </li>
</template>

<script>

  export default {
    name: 'FileTreeRow',

    props: {
      data: { required: true },
      level: { default: 0 },
    },

    data() {
      return {
        is_open: false,
      }
    },

    computed: {
      isDirectory() {
        return Array.isArray(this.data.contents) && this.data.contents.length
      },

      containerClass() {
        return [
          this.isDirectory ? 'file-tree-row--directory' : 'file-tree-row--file',
          this.is_open ? 'file-tree-row--is-open' : '',
        ]
      },

      leftPadding() {
        let padding = (this.level * 36) + 15

        if (!this.isDirectory) {
          padding += 31
        }

        return padding
      },
    },

    methods: {
      toggleOpen() {
        if (this.isDirectory) {
          this.is_open = !this.is_open
        }
      },
    },
  }

</script>

<style lang="scss">

  .file-tree-row {
    list-style: none;
    cursor: pointer;

    &.file-tree-row--is-open {
      > .file-tree-row__title-row > .file-tree-row__arrow svg {
        transform: rotate(90deg);
      }
    }

    ul {
      margin: 0;
      padding: 0;
    }
  }

  .file-tree-row__title-row {
    display: inline-flex;
    align-items: center;
    color: #fff;
    border-bottom: #4d5158 1px solid;
    padding-right: 25px;
    background-color: #35383d;
    min-width: 100%;
    box-sizing: border-box;

    &:hover {
      background-color: #3c3f45;
    }
  }

  .file-tree-row__title {
    line-height: 4rem;
    font-size: 1.2rem;
  }

  .file-tree-row__title-item {
    padding-left: 5px;
    padding-right: 5px;
  }

  .file-tree-row__arrow {
    height: 20px;

    svg {
      transition: transform .1s ease-in;
      height: 15px;
      fill: #fff;
    }
  }

  .file-tree-row__icon {
    height: 20px;

    svg {
      height: 20px;
      fill: #6C7079;

      .fillable {
        fill: #6C7079;
      }
    }
  }

</style>
