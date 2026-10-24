"""Ilustrações SVG originais: paisagens em grade, reprodutíveis e sem dependências.

São assets vetoriais do projeto, não imagens de jogos de terceiros.
Execute novamente se quiser ajustar as paletas ou a composição.
"""
from pathlib import Path
import random

OUT = Path(__file__).resolve().parents[1] / 'public/assets/images'
OUT.mkdir(parents=True, exist_ok=True)

def scene(name, palette, kind, hero=False):
    rng = random.Random(name)
    w, h = (1200, 440) if hero else (640, 380)
    sky, distant, middle, ground, light = palette
    parts = [f'<svg xmlns="http://www.w3.org/2000/svg" width="{w}" height="{h}" viewBox="0 0 {w} {h}" shape-rendering="crispEdges">',
             f'<defs><linearGradient id="sky" x2="0" y2="1"><stop stop-color="{sky}"/><stop offset="1" stop-color="{distant}"/></linearGradient><linearGradient id="water" x2="0" y2="1"><stop stop-color="{middle}"/><stop offset="1" stop-color="{sky}"/></linearGradient></defs>',
             f'<path fill="url(#sky)" d="M0 0h{w}v{h}H0z"/>']
    def rect(x,y,ww,hh,c,opacity=1): parts.append(f'<rect x="{x}" y="{y}" width="{ww}" height="{hh}" fill="{c}" opacity="{opacity}"/>')
    def path(d,c,opacity=1): parts.append(f'<path d="{d}" fill="{c}" opacity="{opacity}"/>')
    for _ in range(100 if hero else 60):
        x,y=rng.randrange(0,w//3)*3,rng.randrange(4,int(h*.65)//3)*3
        rect(x,y,2 if rng.random()<.8 else 4,2,light,rng.choice([.3,.4,.7,1]))
    # Planètes en bandes de pixels, plus anneau et quelques cratères.
    cx,cy,r = (int(w*.75),int(h*.29),76) if hero else (int(w*.70),int(h*.28),57)
    if kind in ('orbit','garden','valley','sea'):
        for yy in range(-r,r,5):
            half=int((max(0,r*r-yy*yy))**.5/5)*5
            rect(cx-half,cy+yy,half*2,5,light if yy < -r*.3 else middle, .85)
        for i in range(9): rect(cx+rng.randrange(-35,35),cy+rng.randrange(-30,35),rng.randrange(2,6)*5,5,distant,.4)
        if kind=='orbit':
            parts.append(f'<ellipse cx="{cx}" cy="{cy+5}" rx="{r*1.8}" ry="18" fill="none" stroke="{light}" stroke-width="5" opacity=".65" transform="rotate(-22 {cx} {cy})"/>')
    # Camadas de montanhas com silhuetas em degraus.
    for layer,base,color in [(0,.48,distant),(1,.64,middle),(2,.79,ground)]:
        d=f'M0 {h} V{int(h*base)}'; prev=int(h*base)
        for x in range(0,w+21,20):
            y=int(h*base+rng.randrange(-65,45)/((layer+1)*.65)); d+=f' H{x} V{y}'
        d+=f' H{w} V{h} Z';path(d,color,.75 if layer==0 else 1)
    if kind=='circuit':
        rect(0,80,w,2,light,.14)
        for x in range(0,w,24):
            bh=rng.randrange(30,130);rect(x,220-bh,18,bh,sky)
            for yy in range(225-bh,210,12): rect(x+5,yy,3,3,light,.6)
        path(f'M{w*.43} 190 H{w*.57} L{w*.88} {h} H{w*.12}Z','#171a35')
        for i in range(9):
            yy=205+i*i*2;rect(w/2-2,yy,4+i//3,3+i,light,.85)
            rect(w*.43-i*i*2,yy,10,3,light,.6);rect(w*.57+i*i*2,yy,10,3,'#f38ec4',.6)
        rect(w*.58,290,57,25,'#e475ad');rect(w*.58+10,280,36,18,'#533d79');rect(w*.58-5,310,14,12,'#121821');rect(w*.58+46,310,14,12,'#121821');rect(w*.58+8,308,12,4,'#ffe1a6');rect(w*.58+33,308,12,4,'#ffe1a6')
    elif kind=='sea':
        rect(0,h*.61,w,h*.39,'url(#water)')
        for _ in range(90): rect(rng.randrange(w),rng.randrange(int(h*.64),h),rng.randrange(10,40),2,light,rng.choice([.1,.2,.3]))
        path('M280 264h100l-20 20h-60z',ground);rect(327,189,5,78,light);path('M320 190v65h-48z','#e8d9b5');path('M340 203v52h39z','#d6b7a0')
    else:
        # Ilhas suspensas / degraus de pedra.
        for x,y,ww in ([(650,260,190),(960,220,130),(490,330,140)] if hero else [(110,260,140),(380,220,100),(470,295,150)]):
            path(f'M{x} {y}h{ww}v15h-15v20h-20v20h-20v25h-20v-15h-25v-20h-20v-20h-20Z',ground)
            rect(x-5,y,ww+10,8,middle);rect(x+5,y-6,ww-10,6,light,.6)
            for _ in range(12):rect(x+rng.randrange(max(ww-10,1)),y+rng.randrange(10,43),5,8,middle,.4)
        # Árvores, folhagens e flores.
        for x in range(10,w,25):
            if rng.random()<.55:
                y=int(h*.90)+rng.randrange(-18,20);height=rng.randrange(15,70)
                rect(x,y-height,5,height,ground);rect(x-12,y-height,30,9,middle);rect(x-7,y-height-8,20,10,middle)
                if kind=='garden':rect(x-5,y-height-14,15,8,light,.8)
        if kind=='fire':
            for x in [35,100,490,550,605]:
                rect(x,30,15,h,ground);path(f'M{x-60} 175l65-140 70 140Z',ground);path(f'M{x-45} 100l50-80 55 80Z',ground)
            rect(305,314,70,8,'#563b31');path('M310 310v-20h10v-20h10v-30h10v30h10v-10h10v35h10v15Z','#df8556');path('M325 310v-20h10v-20h10v25h10v15Z','#ffe6a1')
        # Explorador minúsculo com mochila: escala humana na paisagem.
        px,py=(755,235) if hero else (190,237)
        rect(px,py,10,11,'#ead7ae');rect(px-2,py-4,14,5,light);rect(px-1,py+11,13,17,'#c28a59');rect(px-8,py+12,7,13,middle);rect(px,py+28,4,10,'#172825');rect(px+8,py+28,4,10,'#172825')
        # Folhagem de primeiro plano.
        for x in range(0,w,10):
            yy=h-rng.randrange(3,26);rect(x,yy,10,h-yy,ground)
    # Grão discreto, em blocos, para a textura pixel art.
    for _ in range(180): rect(rng.randrange(w//4)*4,rng.randrange(h//4)*4,4,4,light,.025)
    parts.append('</svg>')
    (OUT/f'{name}.svg').write_text(''.join(parts),encoding='utf-8')

scene('orbit',('#17172f','#3c3159','#84719b','#242d3b','#e6c3dc'),'orbit')
scene('garden',('#102c2c','#28514a','#61967f','#152c2d','#d7e9ab'),'garden')
scene('circuit',('#1a1c35','#3a3457','#705780','#222337','#e6b47a'),'circuit')
scene('valley',('#1c393c','#55786f','#99b897','#263e3b','#f1dfac'),'valley')
scene('fire',('#192535','#36494e','#6a7771','#202e30','#e9b586'),'fire')
scene('sea',('#1b344c','#3d6c80','#699d9f','#283e51','#dcdfbb'),'sea')
scene('hero',('#12272c','#29474a','#57796b','#162e2b','#bdd4a0'),'orbit',True)
print('7 paisagens SVG originais geradas.')
