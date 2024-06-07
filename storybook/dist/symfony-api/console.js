'use strict';

var child_process = require('child_process');

function x(e){for(var t=[],r=1;r<arguments.length;r++)t[r-1]=arguments[r];var n=Array.from(typeof e=="string"?[e]:e);n[n.length-1]=n[n.length-1].replace(/\r?\n([\t ]*)$/,"");var a=n.reduce(function(o,m){var c=m.match(/\n([\t ]+|(?!\s).)/g);return c?o.concat(c.map(function(u){var f,g;return (g=(f=u.match(/[\t ]/g))===null||f===void 0?void 0:f.length)!==null&&g!==void 0?g:0})):o},[]);if(a.length){var s=new RegExp(`
[	 ]{`+Math.min.apply(Math,a)+"}","g");n=n.map(function(o){return o.replace(s,`
`)});}n[0]=n[0].replace(/^\r?\n/,"");var p=n[0];return t.forEach(function(o,m){var c=p.match(/(?:^|\n)( *)$/),u=c?c[1]:"",f=o;typeof o=="string"&&o.includes(`
`)&&(f=String(o).split(`
`).map(function(g,h){return h===0?g:""+u+g}).join(`
`)),p+=f+n[m+1];}),p}var i=x;var S=e=>e.map(({file:t,line:r,function:n,class:a,type:s})=>`at ${a||""}${s||""}${n}() (${t}:${r})`).join(`
`),$=e=>{let t="";try{let r=JSON.parse(e);t+=i`\n
            Error: ${r.error}
            `,r.trace!==void 0&&(t+=i`\n
                Trace: 
                ${S(r.trace)}
                `);}catch{t+=i`\n
        Failed to parse JSON. Output text:
        ${e}
        `;}return t},d=class extends Error{constructor(t,r){t+=i`\n
        ${$(r)}
        `,super(t);}};var v={php:"php",script:"bin/console"},O="storybook:api",y=class extends d{constructor(t,r){let n=i`
                Symfony console failed with exit status ${t.code}.
                CMD: ${t.cmd}
                `;super(n,r);}},l=async(e,t=[],r={})=>{let n={...v,...r},a=[n.php,n.script,`${O}:${e}`].concat(...t).map(s=>`'${s}'`).join(" ");return new Promise((s,p)=>{child_process.exec(a,(o,m,c)=>{o&&p(new y(o,c));try{s(JSON.parse(m));}catch{p(new Error(i`
                    Failed to process JSON output for Symfony command.
                    CMD: ${a}
                    Output: ${m}
                    `));}});})},N=()=>{},j=async()=>l("get-container-parameter",["kernel.project_dir"]),k=async()=>(await l("bundle-config",["twig_component"])).twig_component,B=()=>async()=>l("generate-preview");

exports.generatePreview = B;
exports.getKernelProjectDir = j;
exports.getTwigComponentConfiguration = k;
exports.processConfig = N;
exports.runSymfonyCommand = l;
//# sourceMappingURL=out.js.map
//# sourceMappingURL=console.js.map