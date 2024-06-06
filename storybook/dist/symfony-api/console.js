'use strict';

var child_process = require('child_process');

function h(i){for(var o=[],r=1;r<arguments.length;r++)o[r-1]=arguments[r];var n=Array.from(typeof i=="string"?[i]:i);n[n.length-1]=n[n.length-1].replace(/\r?\n([\t ]*)$/,"");var t=n.reduce(function(e,s){var p=s.match(/\n([\t ]+|(?!\s).)/g);return p?e.concat(p.map(function(u){var m,f;return (f=(m=u.match(/[\t ]/g))===null||m===void 0?void 0:m.length)!==null&&f!==void 0?f:0})):e},[]);if(t.length){var a=new RegExp(`
[	 ]{`+Math.min.apply(Math,t)+"}","g");n=n.map(function(e){return e.replace(a,`
`)});}n[0]=n[0].replace(/^\r?\n/,"");var c=n[0];return o.forEach(function(e,s){var p=c.match(/(?:^|\n)( *)$/),u=p?p[1]:"",m=e;typeof e=="string"&&e.includes(`
`)&&(m=String(e).split(`
`).map(function(f,S){return S===0?f:""+u+f}).join(`
`)),c+=m+n[s+1];}),c}var g=h;var l=i=>i.map(({file:o,line:r,function:n,class:t,type:a})=>`at ${t||""}${a||""}${n}() (${o}:${r})`).join(`
`);var x={php:"php",script:"bin/console"},A="storybook:api",y=class extends Error{constructor(o,r){let n=g`
                Symfony console failed with exit status ${o.code}.
                CMD: ${o.cmd}
                `;try{let t=JSON.parse(r);n+=g`\n
            Error: ${t.error}
            `,t.trace!==void 0&&(n+=g`\n
                Trace:
                ${l(t.trace)}
                `);}catch{n+=g`\n
            Error output: ${r}                    
            `;}super(n);}},d=async(i,o=[],r={})=>{let n={...x,...r},t=[n.php,n.script,`${A}:${i}`].concat(...o).map(a=>`'${a}'`).join(" ");return new Promise((a,c)=>{child_process.exec(t,(e,s,p)=>{e&&c(new y(e,p));try{a(JSON.parse(s));}catch{c(new Error(g`
                Failed to process JSON output for Symfony command.
                CMD: ${t}
                Output: ${s}
                `));}});})},k=()=>{},D=()=>({}),M=async()=>d("get-container-parameter",["kernel.project_dir"]),P=async()=>(await d("bundle-config",["twig_component"])).twig_component,j=async()=>d("generate-preview");

exports.generatePreview = j;
exports.getConfig = D;
exports.getKernelProjectDir = M;
exports.getTwigComponentConfiguration = P;
exports.runSymfonyCommand = d;
exports.setConfig = k;
//# sourceMappingURL=out.js.map
//# sourceMappingURL=console.js.map