/**
 *  
 * @see  https://github.com/squizlabs/PHP_CodeSniffer
 */
 module.exports = function (grunt) {
  'use strict';
  // Project configuration
  grunt.initConfig({
    // Metadata
    pkg: grunt.file.readJSON('package.json'),
    banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
    '<%= grunt.template.today("yyyy-mm-dd") %>\n' +
    '<%= pkg.homepage ? "* " + pkg.homepage + "\\n" : "" %>' +
    '* Copyright (c) <%= grunt.template.today("yyyy") %> <%= pkg.author.name %>;' +
    ' Licensed <%= props.license %> */\n',
    // PHP Coding Standards
    // ====================
    phpcs: {
      core: {
        dir: ['../*.php']
      },
      admin: {
        dir: [
        '../admin/*.php',
        '../admin/*/*.php',
        ]
      },
      includes: {
        dir: [
        '../includes/*.php',
        ]
      },
      shortcodes: {
        dir: [
        '../shortcodes/*.php',
        ]
      },
      globals: {
        dir: [
        '../globals/*.php',
        ]
      },
      helpers: {
        dir: [
        '../helpers/*.php',
        ]
      },

      pages: {
        dir: [
        '../pages/*.php',
        ]
      },

      plugins: {
        dir: [
        '../plugin/*.php',
        ]
      },

      page_templates: {
        dir: [
        '../page-templates/*.php',
        '../page-templates/*/*.php'
        ]
      },

      // leaving this commented out because the 'camel caps format' rule doesn't apply here
      tests: {
        dir: [
        '../tests/*.php',
        ]
      },
      options: {
        // bin: './vendor/bin/phpcbf', // for Fixing coding standards
        bin: './vendor/bin/phpcs',
        standard: [
          'WordPress-Core'
        ],
        ignore: 'header-asu.php'
      }
    },
    
    // PHPUnit
    // =======
    phpunit: {
      configuration: 'phpunit.xml',
      options: {
        bin: './vendor/bin/phpunit',
        color: true
      }
    }
  });

  // These plugins provide necessary tasks
  grunt.loadNpmTasks('grunt-phpcs');
 // grunt.loadNpmTasks('grunt-contrib-csslint');
  grunt.loadNpmTasks('grunt-phpunit');

  // Default task
  grunt.registerTask('default', [
    'phpcs', 
   // 'csslint',
    'phpunit']);
};

