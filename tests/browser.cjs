/* Verificação visual/funcional com Playwright opcional, sem dependência de runtime. */
const {chromium}=require(process.env.PIXELHUB_PLAYWRIGHT || 'playwright');
const fs=require('node:fs');
const path=require('node:path');
(async()=>{
  const browser=await chromium.launch({headless:true,channel:'msedge'});
  const page=await browser.newPage({viewport:{width:1440,height:1100},deviceScaleFactor:1});
  const base=process.argv[2]||'http://127.0.0.1:8081/';
  const errors=[];page.on('pageerror',e=>errors.push(e.message));
  const out=path.join(__dirname,'../test-results');fs.mkdirSync(out,{recursive:true});
  await page.goto(base);await page.screenshot({path:path.join(out,'desktop.png'),fullPage:true});
  if(await page.title()!=='Descobrir · PixelHub')throw Error('Título da home');
  for(const image of await page.locator('img').all())if(!await image.evaluate(el=>el.complete&&el.naturalWidth>0))throw Error('Imagem quebrada');
  await page.goto(base+'index.php?page=catalog');await page.getByRole('textbox',{name:'Buscar',exact:true}).fill('Jardim');await page.getByRole('button',{name:'Filtrar'}).click();
  if(await page.locator('.game-card').count()!==1)throw Error('Filtro visual');
  await page.goto(base+'index.php?page=random');await page.locator('select[name="platform"]').selectOption('Navegador');await page.getByRole('button',{name:'Encontrar minha próxima aventura'}).click();
  if(!await page.getByRole('heading',{name:'Órbita de Papel',exact:true}).isVisible())throw Error('Sorteio visual');
  await page.goto(base+'index.php?page=login');await page.getByLabel('E-mail',{exact:true}).fill('creator@example.test');await page.getByLabel('Senha',{exact:true}).fill('StrongPassword123!');await page.getByRole('button',{name:'Entrar na minha conta'}).click();
  await page.goto(base+'index.php?page=achievements');await page.screenshot({path:path.join(out,'achievements.png'),fullPage:true});
  await page.goto(base+'index.php?page=play');await page.getByRole('button',{name:'Começar aventura'}).click();await page.getByRole('button',{name:'Pausar',exact:true}).click();
  if(!await page.getByText('Aventura pausada.',{exact:true}).isVisible())throw Error('Pausa do minijogo');
  await page.setViewportSize({width:390,height:844});await page.goto(base);await page.screenshot({path:path.join(out,'mobile.png'),fullPage:true});
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Overflow horizontal mobile');
  await page.getByRole('button',{name:'Abrir menu',exact:true}).click();if(!await page.locator('#sidebar').evaluate(el=>el.classList.contains('is-open')))throw Error('Menu mobile');
  await page.keyboard.press('Escape');
  for(const route of ['catalog','game&id=1','random','creator','editor','profile','achievements','collections','play']){
    await page.goto(base+'index.php?page='+route);
    if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Overflow mobile: '+route);
  }
  if(errors.length)throw Error(errors.join('\n'));
  // Mesmo painel com sessão de administrador, em desktop e celular.
  await page.goto(base+'index.php?page=profile');
  await page.getByRole('button',{name:'Sair da conta',exact:true}).click();
  await page.goto(base+'caba%C3%A7o.php');
  await page.getByLabel('E-mail',{exact:true}).fill('admin@example.test');
  await page.getByLabel('Senha',{exact:true}).fill('StrongPassword123!');
  await page.getByRole('button',{name:'Entrar na minha conta'}).click();
  if(!await page.getByRole('heading',{name:'Você no controle.'}).isVisible())throw Error('Retorno ao painel depois do login');
  await page.setViewportSize({width:1440,height:1100});
  await page.screenshot({path:path.join(out,'admin-desktop.png'),fullPage:true});
  await page.setViewportSize({width:390,height:844});
  for(const tab of ['overview','queue','catalog','appearance','users','reports','history']){
    await page.goto(base+'caba%C3%A7o.php?tab='+tab);
    if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Overflow no painel: '+tab);
  }
  await page.goto(base+'caba%C3%A7o.php');
  await page.screenshot({path:path.join(out,'admin-mobile.png'),fullPage:true});
  if(errors.length)throw Error(errors.join('\n'));
  console.log('OK navegador: desktop, celular, imagens, busca, sorteio, login, conquistas e pausa.');
  await browser.close();
})().catch(error=>{console.error(error);process.exit(1)});
