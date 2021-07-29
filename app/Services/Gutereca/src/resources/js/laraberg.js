import 'babel-polyfill'

import './../scss/gutereca.scss'

import './gutenberg/imports'
import init from './gutenberg/init'
import { getContent, setContent } from './lib/content'
import { registerBlock, registerCategory } from './lib/custom-blocks'

const Gutereca = {
  init: init,
  initGutenberg: init,
  getContent: getContent,
  setContent: setContent,
  editor: null,
  registerCategory: registerCategory,
  registerBlock: registerBlock
}

window.Gutereca = Gutereca

export default Gutereca
