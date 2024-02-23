import {copyFile} from 'copy-file';

await copyFile('README.md', 'docs/index.md');
