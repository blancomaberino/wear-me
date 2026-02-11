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
};

export default es;
