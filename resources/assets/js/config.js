/**
 * Config
 * -------------------------------------------------------------------------------------
 * ! IMPORTANT: Make sure you clear the browser local storage In order to see the config changes in the template.
 * ! To clear local storage: (https://www.leadshook.com/help/how-to-clear-local-storage-in-google-chrome-browser/).
 */

'use strict';

// JS global variables
let config = {
  colors: {
    primary: window.Helpers.getCssVar('primary', '#003975'),
    secondary: window.Helpers.getCssVar('secondary', '#8592A3'),
    success: window.Helpers.getCssVar('success', '#71DD37'),
    info: window.Helpers.getCssVar('info', '#03C3EC'),
    warning: window.Helpers.getCssVar('warning', '#FFAB00'),
    danger: window.Helpers.getCssVar('danger', '#FF3E1D'),
    dark: window.Helpers.getCssVar('dark', '#233446'),
    black: window.Helpers.getCssVar('black', '#000'),
    white: window.Helpers.getCssVar('white', '#fff'),
    cardColor: window.Helpers.getCssVar('card-bg', '#fff'),
    bodyBg: window.Helpers.getCssVar('body-bg', '#F4F5FA'),
    bodyColor: window.Helpers.getCssVar('body-color', '#697A8D'),
    textMuted: window.Helpers.getCssVar('text-muted', '#A1ACB8'),
  }
};