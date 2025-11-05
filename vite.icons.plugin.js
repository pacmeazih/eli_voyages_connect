import { getIconsCSS } from '@iconify/utils';
import fs from 'fs/promises';
import path from 'path';

export default function iconifyPlugin() {
  return {
    name: 'iconify-plugin',
    
    async buildStart() {
      console.log('🔨 Generating iconify CSS file...');

      try {
        // Use Remix Icons by default
        const iconSetPaths = [path.resolve(process.cwd(), 'node_modules/@iconify/json/json/ri.json')];

        const iconSets = await Promise.all(
          iconSetPaths.map(async filePath => {
            const data = await fs.readFile(filePath, 'utf-8');
            return JSON.parse(data);
          })
        );

        const allIcons = iconSets
          .map(iconSet => {
            return getIconsCSS(iconSet, Object.keys(iconSet.icons), {
              iconSelector: '.{prefix}-{name}',
              commonSelector: '.ri',
              format: 'expanded'
            });
          })
          .join('\n');

        const outputPath = path.resolve(process.cwd(), 'resources/assets/vendor/fonts/iconify/iconify.css');
        const dir = path.dirname(outputPath);
        await fs.mkdir(dir, { recursive: true });
        await fs.writeFile(outputPath, allIcons, 'utf8');

        console.log('✨ Generated iconify CSS file');
      } catch (error) {
        console.error('Error generating iconify CSS:', error);
      }
    }
  };
}