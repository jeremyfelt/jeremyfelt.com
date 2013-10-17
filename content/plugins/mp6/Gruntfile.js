module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    meta: {
      colors: 'components/color-schemes',
      customizer: 'components/customizer'
    },

    sass: {
      admin: {
        options: {
          style: 'compact',
          lineNumbers: false
        },
        files : {
          '<%= meta.colors %>/picker/style.css'           : '<%= meta.colors %>/picker/style.scss',
          '<%= meta.customizer %>/customizer.css' : '<%= meta.customizer %>/scss/customizer.scss',
        }
      },
      colors: {
      	options: {
      	  style: 'compact',
      	  lineNumbers: false,
      	},
        files: [{
          '<%= meta.colors %>/schemes/80s-kid/admin-colors.css': ['<%= meta.colors %>/schemes/80s-kid/colors.scss', '<%= meta.colors %>/schemes/_admin.scss'],
          '<%= meta.colors %>/schemes/80s-kid/customizer.css': ['<%= meta.colors %>/schemes/80s-kid/colors.scss', '<%= meta.colors %>/schemes/_customizer.scss'],

          '<%= meta.colors %>/schemes/blue/admin-colors.css': ['<%= meta.colors %>/schemes/blue/colors.scss', '<%= meta.colors %>/schemes/_admin.scss'],
          '<%= meta.colors %>/schemes/blue/customizer.css': ['<%= meta.colors %>/schemes/blue/colors.scss', '<%= meta.colors %>/schemes/_customizer.scss'],

          '<%= meta.colors %>/schemes/ectoplasm/admin-colors.css': ['<%= meta.colors %>/schemes/ectoplasm/colors.scss', '<%= meta.colors %>/schemes/_admin.scss'],
          '<%= meta.colors %>/schemes/ectoplasm/customizer.css': ['<%= meta.colors %>/schemes/ectoplasm/colors.scss', '<%= meta.colors %>/schemes/_customizer.scss'],

          '<%= meta.colors %>/schemes/lioness/admin-colors.css': ['<%= meta.colors %>/schemes/lioness/colors.scss', '<%= meta.colors %>/schemes/_admin.scss'],
          '<%= meta.colors %>/schemes/lioness/customizer.css': ['<%= meta.colors %>/schemes/lioness/colors.scss', '<%= meta.colors %>/schemes/_customizer.scss'],

          '<%= meta.colors %>/schemes/malibu-dreamhouse/admin-colors.css': ['<%= meta.colors %>/schemes/malibu-dreamhouse/colors.scss', '<%= meta.colors %>/schemes/_admin.scss'],
          '<%= meta.colors %>/schemes/malibu-dreamhouse/customizer.css': ['<%= meta.colors %>/schemes/malibu-dreamhouse/colors.scss', '<%= meta.colors %>/schemes/_customizer.scss'],

          '<%= meta.colors %>/schemes/mp6-light/admin-colors.css': ['<%= meta.colors %>/schemes/mp6-light/colors.scss', '<%= meta.colors %>/schemes/_admin.scss', '<%= meta.colors %>/schemes/mp6-light/custom.scss'],
          '<%= meta.colors %>/schemes/mp6-light/customizer.css': ['<%= meta.colors %>/schemes/mp6-light/colors.scss', '<%= meta.colors %>/schemes/_customizer.scss'],

          '<%= meta.colors %>/schemes/pixel/admin-colors.css': ['<%= meta.colors %>/schemes/pixel/colors.scss', '<%= meta.colors %>/schemes/_admin.scss'],
          '<%= meta.colors %>/schemes/pixel/customizer.css': ['<%= meta.colors %>/schemes/pixel/colors.scss', '<%= meta.colors %>/schemes/_customizer.scss'],

          '<%= meta.colors %>/schemes/seaweed/admin-colors.css': ['<%= meta.colors %>/schemes/seaweed/colors.scss', '<%= meta.colors %>/schemes/_admin.scss'],
          '<%= meta.colors %>/schemes/seaweed/customizer.css': ['<%= meta.colors %>/schemes/seaweed/colors.scss', '<%= meta.colors %>/schemes/_customizer.scss'],
          
          '<%= meta.colors %>/schemes/midnight/admin-colors.css': ['<%= meta.colors %>/schemes/midnight/colors.scss', '<%= meta.colors %>/schemes/_admin.scss'],
          '<%= meta.colors %>/schemes/midnight/customizer.css': ['<%= meta.colors %>/schemes/midnight/colors.scss', '<%= meta.colors %>/schemes/_customizer.scss'],
        }]
      }
    },

    watch: {
      sass: {
        files: ['<%= meta.colors %>/**/*.scss', ],
        tasks: ['sass:colors']
      },
      sass: {
        files: ['<%= meta.colors %>/picker/style.scss', '<%= meta.customizer %>/scss/*.scss' ],
        tasks: ['sass:admin']
      }
    }

  });

  grunt.loadNpmTasks('grunt-contrib-sass');
  grunt.loadNpmTasks('grunt-contrib-watch');

  // Default task(s).
  grunt.registerTask('default', ['sass']);

};
