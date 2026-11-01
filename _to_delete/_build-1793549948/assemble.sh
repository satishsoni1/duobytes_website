set -e
W="$HOME/mnt/website"; B="$W/_build"; cd "$W"
# 1. move School ERP into its own folder (copy, root is replaced below)
mkdir -p school-erp
[ -f school-erp/index.html ] || cp -R index.html css js assets school-erp/
BASE=school-erp/css/style.css
# 2. product sites hms + ffrs
theme(){ sed -e "s/--orange:#F36F21/--orange:$2/" -e "s/--amber:#FBB321/--amber:$3/" -e "s/#FFF1E6/$4/g" -e "s/rgba(243,111,33/rgba($5/g" -e "s/rgba(251,179,33/rgba($6/g" "$1"; }
for p in hms ffrs; do
  mkdir -p $p/css $p/js $p/assets
  cp school-erp/js/lucide.min.js $p/js/
  cp "$B/$p-index.html" $p/index.html; cp "$B/$p-main.js" $p/js/main.js
  cp "$B/logos/$p-mark.png" $p/assets/logo-mark.png; cp "$B/logos/$p-full.png" $p/assets/logo-full.png
done
theme $BASE '#0E9F7E' '#34D399' '#E6F7F1' '14,159,126' '52,211,153' > hms/css/style.css
cat "$B/sales-extra.css" "$B/theme-hms.css" >> hms/css/style.css
theme $BASE '#7C3AED' '#C084FC' '#F3EBFF' '124,58,237' '192,132,252' > ffrs/css/style.css
cat "$B/sales-extra.css" "$B/theme-ffrs.css" >> ffrs/css/style.css
# 3. sales-erp: use ERP logo
cp "$B/logos/erp-mark.png" sales-erp/assets/logo-mark.png; cp "$B/logos/erp-full.png" sales-erp/assets/logo-full.png
# 4. company root
cp "$B/company-index.html" index.html; cp "$B/company-main.js" js/main.js
cp $BASE css/style.css; cat "$B/company-extra.css" >> css/style.css
cp "$B/logos/company-mark.png" assets/company-mark.png; cp "$B/logos/company-full.png" assets/company-full.png
echo assembled
