import common from './common';
import nav from './nav';
import auth from './auth';
import dashboard from './dashboard';
import photos from './photos';
import wardrobe from './wardrobe';
import tryon from './tryon';
import profile from './profile';
import outfits from './outfits';
import welcome from './welcome';
import processing from './processing';
import videos from './videos';
import importNs from './import';
import lookbooks from './lookbooks';
import share from './share';
import packing from './packing';
import exportNs from './export';

const es = {
  ...Object.fromEntries(Object.entries(common).map(([k, v]) => [`common.${k}`, v])),
  ...Object.fromEntries(Object.entries(nav).map(([k, v]) => [`nav.${k}`, v])),
  ...Object.fromEntries(Object.entries(auth).map(([k, v]) => [`auth.${k}`, v])),
  ...Object.fromEntries(Object.entries(dashboard).map(([k, v]) => [`dashboard.${k}`, v])),
  ...Object.fromEntries(Object.entries(photos).map(([k, v]) => [`photos.${k}`, v])),
  ...Object.fromEntries(Object.entries(wardrobe).map(([k, v]) => [`wardrobe.${k}`, v])),
  ...Object.fromEntries(Object.entries(tryon).map(([k, v]) => [`tryon.${k}`, v])),
  ...Object.fromEntries(Object.entries(profile).map(([k, v]) => [`profile.${k}`, v])),
  ...Object.fromEntries(Object.entries(outfits).map(([k, v]) => [`outfits.${k}`, v])),
  ...Object.fromEntries(Object.entries(welcome).map(([k, v]) => [`welcome.${k}`, v])),
  ...Object.fromEntries(Object.entries(processing).map(([k, v]) => [`processing.${k}`, v])),
  ...Object.fromEntries(Object.entries(videos).map(([k, v]) => [`videos.${k}`, v])),
  ...Object.fromEntries(Object.entries(importNs).map(([k, v]) => [`import.${k}`, v])),
  ...Object.fromEntries(Object.entries(lookbooks).map(([k, v]) => [`lookbooks.${k}`, v])),
  ...Object.fromEntries(Object.entries(share).map(([k, v]) => [`share.${k}`, v])),
  ...Object.fromEntries(Object.entries(packing).map(([k, v]) => [`packing.${k}`, v])),
  ...Object.fromEntries(Object.entries(exportNs).map(([k, v]) => [`export.${k}`, v])),
};

export default es;
