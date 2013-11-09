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
        files : [{
          expand: true,
          cwd: '<%= meta.colors %>/schemes',
          src: [ '*/*.scss' ],
          dest: '<%= meta.colors %>/schemes',
          ext: '.css',
          rename: function ( path, dest ) {
            // Change the generated filename
            return path + '/' + dest.replace('colors.css','admin-colors.css');
          }
        }]
      }
    },

    watch: {
      sassColors: {
        files: ['<%= meta.colors %>/**/*.scss', ],
        tasks: ['sass:colors']
      },
      sassAdmin: {
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
