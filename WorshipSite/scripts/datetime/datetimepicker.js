/ / J a v a s c r i p t   n a m e :   M y   D a t e   T i m e   P i c k e r 
 / / D a t e   c r e a t e d :   1 6 - N o v - 2 0 0 3   2 3 : 1 9 
 / / S c r i p t e r :   T e n g Y o n g   N g 
 / / W e b s i t e :   h t t p : / / w w w . r a i n f o r e s t n e t . c o m 
 / / C o p y r i g h t   ( c )   2 0 0 3   T e n g Y o n g   N g 
 / / F i l e N a m e :   D a t e T i m e P i c k e r _ c s s . j s 
 / / V e r s i o n :   2 . 1 
 / /   N o t e :   P e r m i s s i o n   g i v e n   t o   u s e   a n d   m o d i f y   t h i s   s c r i p t   i n   A N Y   k i n d   o f   a p p l i c a t i o n s   i f 
 / /               h e a d e r   l i n e s   a r e   l e f t   u n c h a n g e d . 
 / / D a t e   c h a n g e d :   2 4 - D e c - 2 0 0 7   b y   B u r g s o f t   ( H o l l a n d ) 
 / / C h a n g e d :   Y e a r   p i c k e r   a s   d r o p   d o w n .   C o d e   o p t i m i s e d .   T a b l e s   f i l l e d   w i t h   b l a n k   f i e l d s   a s   n e e d e d . 
 / / K n o w n   ( n o n   f a t a l )   i s s u e :   j a v a s c r i p t   r e m a i n s   r u n n i n g   a f t e r   m o n t h   o r   y e a r   s e l e c t 
 / / N e w   C s s   s t y l e   v e r s i o n   a d d e d   b y   Y v a n   L a v o i e   ( Q u � b e c ,   C a n a d a )   2 9 - J a n - 2 0 0 9 
 
 / / G l o b a l   v a r i a b l e s 
 v a r   w i n C a l ; 
 v a r   d t T o d a y ; 
 v a r   C a l ; 
 
 v a r   M o n t h N a m e ; 
 v a r   W e e k D a y N a m e 1 ; 
 v a r   W e e k D a y N a m e 2 ; 
 
 v a r   e x D a t e T i m e ; / / E x i s t i n g   D a t e   a n d   T i m e 
 v a r   s e l D a t e ; / / s e l e c t e d   d a t e .   v e r s i o n   1 . 7 
 
 v a r   c a l S p a n I D   =   " c a l B o r d e r " ;   / /   s p a n   I D   
 v a r   d o m S t y l e = n u l l ;   / /   s p a n   D O M   o b j e c t   w i t h   s t y l e   
 v a r   c n L e f t = " 0 " ; / / l e f t   c o o r d i n a t e   o f   c a l e n d a r   s p a n 
 v a r   c n T o p = " 0 " ; / / t o p   c o o r d i n a t e   o f   c a l e n d a r   s p a n 
 v a r   x p o s = 0 ;   / /   m o u s e   x   p o s i t i o n 
 v a r   y p o s = 0 ;   / /   m o u s e   y   p o s i t i o n 
 v a r   c a l H e i g h t = 0 ;   / /   c a l e n d a r   h e i g h t 
 v a r   C a l W i d t h = 2 0 8 ; / /   c a l e n d a r   w i d t h 
 v a r   C e l l W i d t h = 3 0 ; / /   w i d t h   o f   d a y   c e l l . 
 v a r   T i m e M o d e = 2 4 ; / /   T i m e M o d e   v a l u e .   1 2   o r   2 4 
 
 / / C o n f i g u r a b l e   p a r a m e t e r s 
 
 / / v a r   W i n d o w T i t l e = " D a t e T i m e   P i c k e r " ; / / D a t e   T i m e   P i c k e r   t i t l e . 
 v a r   S p a n B o r d e r C o l o r   =   " # c d c d c d " ; / / s p a n   b o r d e r   c o l o r   
 v a r   S p a n B g C o l o r   =   " # c d c d c d " ; / / s p a n   b a c k g r o u n d   c o l o r 
 v a r   W e e k C h a r = 2 ; / / n u m b e r   o f   c h a r a c t e r   f o r   w e e k   d a y .   i f   2   t h e n   M o , T u , W e .   i f   3   t h e n   M o n , T u e , W e d . 
 v a r   D a t e S e p a r a t o r = " - " ; / / D a t e   S e p a r a t o r ,   y o u   c a n   c h a n g e   i t   t o   " - "   i f   y o u   w a n t . 
 v a r   S h o w L o n g M o n t h = t r u e ; / / S h o w   l o n g   m o n t h   n a m e   i n   C a l e n d a r   h e a d e r .   e x a m p l e :   " J a n u a r y " . 
 v a r   S h o w M o n t h Y e a r = t r u e ; / / S h o w   M o n t h   a n d   Y e a r   i n   C a l e n d a r   h e a d e r . 
 v a r   M o n t h Y e a r C o l o r = " # c c 0 0 3 3 " ; / / F o n t   C o l o r   o f   M o n t h   a n d   Y e a r   i n   C a l e n d a r   h e a d e r . 
 v a r   W e e k H e a d C o l o r = " # 1 8 8 6 1 B " ; / / B a c k g r o u n d   C o l o r   i n   W e e k   h e a d e r . 
 v a r   S u n d a y C o l o r = " # C 0 F 6 4 F " ; / / B a c k g r o u n d   c o l o r   o f   S u n d a y . 
 v a r   S a t u r d a y C o l o r = " # C 0 F 6 4 F " ; / / B a c k g r o u n d   c o l o r   o f   S a t u r d a y . 
 v a r   W e e k D a y C o l o r = " w h i t e " ; / / B a c k g r o u n d   c o l o r   o f   w e e k d a y s . 
 v a r   F o n t C o l o r = " b l u e " ; / / c o l o r   o f   f o n t   i n   C a l e n d a r   d a y   c e l l . 
 v a r   T o d a y C o l o r = " # F F F F 3 3 " ; / / B a c k g r o u n d   c o l o r   o f   t o d a y . 
 v a r   S e l D a t e C o l o r = " # 8 D D 5 3 C " ; / / B a c k g r o n d   c o l o r   o f   s e l e c t e d   d a t e   i n   t e x t b o x . 
 v a r   Y r S e l C o l o r = " # c c 0 0 3 3 " ; / / c o l o r   o f   f o n t   o f   Y e a r   s e l e c t o r . 
 v a r   M t h S e l C o l o r = " # c c 0 0 3 3 " ; / / c o l o r   o f   f o n t   o f   M o n t h   s e l e c t o r   i f   " M o n t h S e l e c t o r "   i s   " a r r o w " . 
 v a r   T h e m e B g = " " ; / / B a c k g r o u n d   i m a g e   o f   C a l e n d a r   w i n d o w . 
 v a r   C a l B g C o l o r = " " ; / / B a c k g r o u d   c o l o r   o f   C a l e n d a r   w i n d o w . 
 v a r   P r e c e d e Z e r o = t r u e ; / / P r e c e d i n g   z e r o   [ t r u e | f a l s e ] 
 v a r   M o n d a y F i r s t D a y = f a l s e ; / / t r u e : U s e   M o n d a y   a s   f i r s t   d a y ;   f a l s e : S u n d a y   a s   f i r s t   d a y .   [ t r u e | f a l s e ]     / / a d d e d   i n   v e r s i o n   1 . 7 
 v a r   U s e I m a g e F i l e s   =   t r u e ; / / U s e   i m a g e   f i l e s   w i t h   " a r r o w s "   a n d   " c l o s e "   b u t t o n 
 / / u s e   t h e   M o n t h   a n d   W e e k d a y   i n   y o u r   p r e f e r r e d   l a n g u a g e . 
 v a r   M o n t h N a m e = [ " J a n u a r y " ,   " F e b r u a r y " ,   " M a r c h " ,   " A p r i l " ,   " M a y " ,   " J u n e " , " J u l y " , " A u g u s t " ,   " S e p t e m b e r " ,   " O c t o b e r " ,   " N o v e m b e r " ,   " D e c e m b e r " ] ; 
 v a r   W e e k D a y N a m e 1 = [ " S u n d a y " , " M o n d a y " , " T u e s d a y " , " W e d n e s d a y " , " T h u r s d a y " , " F r i d a y " , " S a t u r d a y " ] ;  
 v a r   W e e k D a y N a m e 2 = [ " M o n d a y " , " T u e s d a y " , " W e d n e s d a y " , " T h u r s d a y " , " F r i d a y " , " S a t u r d a y " , " S u n d a y " ] ; 
 
 / / e n d   C o n f i g u r a b l e   p a r a m e t e r s 
 / / e n d   G l o b a l   v a r i a b l e 
 
 / /   D e f a u l t   e v e n t s   c o n f i g u r a t i o n 
 d o c u m e n t . o n m o u s e d o w n   =   p i c k I t ; 
 d o c u m e n t . o n m o u s e m o v e   =   d r a g I t ; 
 d o c u m e n t . o n m o u s e u p   =   d r o p I t ; 
 
 f u n c t i o n   N e w C s s C a l ( p C t r l , p F o r m a t , p S c r o l l e r , p S h o w T i m e , p T i m e M o d e , p H i d e S e c o n d s )   { 
 	 / /   g e t   c u r r e n t   d a t e   a n d   t i m e 
 	 d t T o d a y   =   n e w   D a t e ( ) ; 
 	 C a l = n e w   C a l e n d a r ( d t T o d a y ) ; 
 	 
 	 i f   ( ( p S h o w T i m e ! = n u l l )   & &   ( p S h o w T i m e ) )   { 
 	 	 C a l . S h o w T i m e = t r u e ; 
 	 	 i f   ( ( p T i m e M o d e ! = n u l l )   & & ( ( p T i m e M o d e = = ' 1 2 ' ) | | ( p T i m e M o d e = = ' 2 4 ' ) ) ) 	 { 
 	 	 	 T i m e M o d e = p T i m e M o d e ; 
 	 	 } 
 	 	 e l s e   T i m e M o d e = ' 2 4 ' ; 
 
                 i f   ( p H i d e S e c o n d s ! = n u l l ) 
                 { 
                         i f   ( p H i d e S e c o n d s ) 
                         { C a l . S h o w S e c o n d s = f a l s e ; } 
                         e l s e 
                         { C a l . S h o w S e c o n d s = t r u e ; } 
                 } 
                 e l s e 
                 { 
                         C a l . S h o w S e c o n d s = f a l s e ; 
                 }         
 	 } 
 	 i f   ( p C t r l ! = n u l l ) 
 	 	 C a l . C t r l = p C t r l ; 
 	 
 	 i f   ( p F o r m a t ! = n u l l ) 
 	 	 C a l . F o r m a t = p F o r m a t . t o U p p e r C a s e ( ) ; 
 	 e l s e   
 	         C a l . F o r m a t = " M M D D Y Y Y Y " ; 
 	 
 	 i f   ( p S c r o l l e r ! = n u l l )   { 
 	 	 i f   ( p S c r o l l e r . t o U p p e r C a s e ( ) = = " A R R O W " )   { 
 	 	 	 C a l . S c r o l l e r = " A R R O W " ; 
 	 	 } 
 	 	 e l s e   { 
 	 	 	 C a l . S c r o l l e r = " D R O P D O W N " ; 
 	 	 } 
         } 
 	 e x D a t e T i m e = d o c u m e n t . g e t E l e m e n t B y I d ( p C t r l ) . v a l u e ; 
 	 
 	 i f   ( e x D a t e T i m e ! = " " ) 	 {   / / P a r s e   e x i s t i n g   D a t e   S t r i n g 
 	 	 v a r   S p 1 ; / / I n d e x   o f   D a t e   S e p a r a t o r   1 
 	 	 v a r   S p 2 ; / / I n d e x   o f   D a t e   S e p a r a t o r   2   
 	 	 v a r   t S p 1 ; / / I n d e x   o f   T i m e   S e p a r a t o r   1 
 	 	 v a r   t S p 1 ; / / I n d e x   o f   T i m e   S e p a r a t o r   2 
 	 	 v a r   s t r M o n t h ; 
 	 	 v a r   s t r D a t e ; 
 	 	 v a r   s t r Y e a r ; 
 	 	 v a r   i n t M o n t h ; 
 	 	 v a r   Y e a r P a t t e r n ; 
 	 	 v a r   s t r H o u r ; 
 	 	 v a r   s t r M i n u t e ; 
 	 	 v a r   s t r S e c o n d ; 
 	 	 v a r   w i n H e i g h t ; 
 	 	 / / p a r s e   m o n t h 
 	 	 S p 1 = e x D a t e T i m e . i n d e x O f ( D a t e S e p a r a t o r , 0 ) 
 	 	 S p 2 = e x D a t e T i m e . i n d e x O f ( D a t e S e p a r a t o r , ( p a r s e I n t ( S p 1 ) + 1 ) ) ; 
 	 	 
 	 	 v a r   o f f s e t = p a r s e I n t ( C a l . F o r m a t . t o U p p e r C a s e ( ) . l a s t I n d e x O f ( " M " ) ) - p a r s e I n t ( C a l . F o r m a t . t o U p p e r C a s e ( ) . i n d e x O f ( " M " ) ) - 1 ; 
 	 	 i f   ( ( C a l . F o r m a t . t o U p p e r C a s e ( ) = = " D D M M Y Y Y Y " )   | |   ( C a l . F o r m a t . t o U p p e r C a s e ( ) = = " D D M M M Y Y Y Y " ) )   { 
 	 	 	 i f   ( D a t e S e p a r a t o r = = " " )   { 
 	 	 	 	 s t r M o n t h = e x D a t e T i m e . s u b s t r i n g ( 2 , 4 + o f f s e t ) ; 
 	 	 	 	 s t r D a t e = e x D a t e T i m e . s u b s t r i n g ( 0 , 2 ) ; 
 	 	 	 	 s t r Y e a r = e x D a t e T i m e . s u b s t r i n g ( 4 + o f f s e t , 8 + o f f s e t ) ; 
 	 	 	 } 
 	 	 	 e l s e   { 
 	 	 	 	 s t r M o n t h = e x D a t e T i m e . s u b s t r i n g ( S p 1 + 1 , S p 2 ) ; 
 	 	 	 	 s t r D a t e = e x D a t e T i m e . s u b s t r i n g ( 0 , S p 1 ) ; 
 	 	 	 	 s t r Y e a r = e x D a t e T i m e . s u b s t r i n g ( S p 2 + 1 , S p 2 + 5 ) ; 
 	 	 	 } 
 	 	 } 
 	 	 e l s e   i f   ( ( C a l . F o r m a t . t o U p p e r C a s e ( ) = = " M M D D Y Y Y Y " )   | |   ( C a l . F o r m a t . t o U p p e r C a s e ( ) = = " M M M D D Y Y Y Y " ) )   { 
 	 	 	 i f   ( D a t e S e p a r a t o r = = " " )   { 
 	 	 	 	 s t r M o n t h = e x D a t e T i m e . s u b s t r i n g ( 0 , 2 + o f f s e t ) ; 
 	 	 	 	 s t r D a t e = e x D a t e T i m e . s u b s t r i n g ( 2 + o f f s e t , 4 + o f f s e t ) ; 
 	 	 	 	 s t r Y e a r = e x D a t e T i m e . s u b s t r i n g ( 4 + o f f s e t , 8 + o f f s e t ) ; 
 	 	 	 } 
 	 	 	 e l s e   { 
 	 	 	 	 s t r M o n t h = e x D a t e T i m e . s u b s t r i n g ( 0 , S p 1 ) ; 
 	 	 	 	 s t r D a t e = e x D a t e T i m e . s u b s t r i n g ( S p 1 + 1 , S p 2 ) ; 
 	 	 	 	 s t r Y e a r = e x D a t e T i m e . s u b s t r i n g ( S p 2 + 1 , S p 2 + 5 ) ; 
 	 	 	 } 
 	 	 } 
 	 	 e l s e   i f   ( ( C a l . F o r m a t . t o U p p e r C a s e ( ) = = " Y Y Y Y M M D D " )   | |   ( C a l . F o r m a t . t o U p p e r C a s e ( ) = = " Y Y Y Y M M M D D " ) )   { 
 	 	 	 i f   ( D a t e S e p a r a t o r = = " " )   { 
 	 	 	 	 s t r M o n t h = e x D a t e T i m e . s u b s t r i n g ( 4 , 6 + o f f s e t ) ; 
 	 	 	 	 s t r D a t e = e x D a t e T i m e . s u b s t r i n g ( 6 + o f f s e t , 8 + o f f s e t ) ; 
 	 	 	 	 s t r Y e a r = e x D a t e T i m e . s u b s t r i n g ( 0 , 4 ) ; 
 	 	 	 } 
 	 	 	 e l s e   { 
 	 	 	 	 s t r M o n t h = e x D a t e T i m e . s u b s t r i n g ( S p 1 + 1 , S p 2 ) ; 
 	 	 	 	 s t r D a t e = e x D a t e T i m e . s u b s t r i n g ( S p 2 + 1 , S p 2 + 3 ) ; 
 	 	 	 	 s t r Y e a r = e x D a t e T i m e . s u b s t r i n g ( 0 , S p 1 ) ; 
 	 	 	 } 
 	 	 } 
 	 	 i f   ( i s N a N ( s t r M o n t h ) ) 
 	 	 	 i n t M o n t h = C a l . G e t M o n t h I n d e x ( s t r M o n t h ) ; 
 	 	 e l s e 
 	 	 	 i n t M o n t h = p a r s e I n t ( s t r M o n t h , 1 0 ) - 1 ; 	 
 	 	 i f   ( ( p a r s e I n t ( i n t M o n t h , 1 0 ) > = 0 )   & &   ( p a r s e I n t ( i n t M o n t h , 1 0 ) < 1 2 ) ) 
 	 	 	 C a l . M o n t h = i n t M o n t h ; 
 	 	 / / e n d   p a r s e   m o n t h 
 	 	 / / p a r s e   D a t e 
 	 	 i f   ( ( p a r s e I n t ( s t r D a t e , 1 0 ) < = C a l . G e t M o n D a y s ( ) )   & &   ( p a r s e I n t ( s t r D a t e , 1 0 ) > = 1 ) ) 
 	 	 	 C a l . D a t e = s t r D a t e ; 
 	 	 / / e n d   p a r s e   D a t e 
 	 	 / / p a r s e   y e a r 
 	 	 Y e a r P a t t e r n = / ^ \ d { 4 } $ / ; 
 	 	 i f   ( Y e a r P a t t e r n . t e s t ( s t r Y e a r ) ) 
 	 	 	 C a l . Y e a r = p a r s e I n t ( s t r Y e a r , 1 0 ) ; 
 	 	 / / e n d   p a r s e   y e a r 
 	 	 / / p a r s e   t i m e 
 	 	 i f   ( C a l . S h o w T i m e = = t r u e ) 	 { 
 	 	 	 / / p a r s e   A M   o r   P M 
 	 	 	 i f   ( T i m e M o d e = = 1 2 )   { 
 	 	 	 	 s t r A M P M = e x D a t e T i m e . s u b s t r i n g ( e x D a t e T i m e . l e n g t h - 2 , e x D a t e T i m e . l e n g t h ) 
 	 	 	 	 C a l . A M o r P M = s t r A M P M ; 
 	 	 	 } 
 	 	 	 t S p 1 = e x D a t e T i m e . i n d e x O f ( " : " , 0 ) 
 	 	 	 t S p 2 = e x D a t e T i m e . i n d e x O f ( " : " , ( p a r s e I n t ( t S p 1 ) + 1 ) ) ; 
 	 	 	 i f   ( t S p 1 > 0 ) 	 { 
 	 	 	 	 s t r H o u r = e x D a t e T i m e . s u b s t r i n g ( t S p 1 , ( t S p 1 ) - 2 ) ; 
 	 	 	 	 C a l . S e t H o u r ( s t r H o u r ) ; 
 	 	 	 	 s t r M i n u t e = e x D a t e T i m e . s u b s t r i n g ( t S p 1 + 1 , t S p 1 + 3 ) ; 
 	 	 	 	 C a l . S e t M i n u t e ( s t r M i n u t e ) ; 
 	 	 	 	 s t r S e c o n d = e x D a t e T i m e . s u b s t r i n g ( t S p 2 + 1 , t S p 2 + 3 ) ; 
 	 	 	 	 C a l . S e t S e c o n d ( s t r S e c o n d ) ; 
 	 	 	 } 
 	 	 } 	 
 	 } 
 	 s e l D a t e = n e w   D a t e ( C a l . Y e a r , C a l . M o n t h , C a l . D a t e ) ; / / v e r s i o n   1 . 7 
 	 R e n d e r C s s C a l ( t r u e ) ; 
 } 
 
 f u n c t i o n   R e n d e r C s s C a l ( b N e w C a l )   { 
 	 i f   ( t y p e o f   b N e w C a l   = =   " u n d e f i n e d "   | |   b N e w C a l   ! =   t r u e )   { b N e w C a l   =   f a l s e ; } 
 	 v a r   v C a l H e a d e r ; 
 	 v a r   v C a l D a t a ; 
 	 v a r   v C a l T i m e = " " ; 
 	 v a r   i ; 
 	 v a r   j ; 
 	 v a r   S e l e c t S t r ; 
 	 v a r   v D a y C o u n t = 0 ; 
 	 v a r   v F i r s t D a y ; 
 	 c a l H e i g h t   =   0 ;   / /   r e s e t   t h e   w i n d o w   h e i g h t   o n   r e f r e s h 
 	 
 	 / /   S e t   t h e   d e f a u l t   c u r s o r   f o r   t h e   c a l e n d a r 
 	 w i n C a l D a t a = " < s p a n   s t y l e = ' c u r s o r : a u t o ; ' > \ n " ; 
 	 
 	 i f   ( T h e m e B g = = " " ) { C a l B g C o l o r = " b g c o l o r = ' " + W e e k D a y C o l o r + " ' " } 
                 
 	 v C a l H e a d e r = " < t a b l e   " + C a l B g C o l o r + "   b a c k g r o u n d = ' " + T h e m e B g + " '   b o r d e r = 1   c e l l p a d d i n g = 1   c e l l s p a c i n g = 1   w i d t h = ' 2 0 0 '   v a l i g n = ' t o p ' > \ n " ; 
 	 / / T a b l e   f o r   M o n t h   &   Y e a r   S e l e c t o r 
 	 v C a l H e a d e r + = " < t r > \ n < t d   c o l s p a n = ' 7 ' > \ n < t a b l e   b o r d e r = 0   w i d t h = 2 0 0   c e l l p a d d i n g = 0   c e l l s p a c i n g = 0 > \ n < t r > \ n " ; 
 
 	 / / * * * * * * * * * * * * * * * * * * M o n t h   a n d   Y e a r   s e l e c t o r   i n   d r o p d o w n   l i s t * * * * * * * * * * * * * * * * * * * * * * * * 
 	 i f   ( C a l . S c r o l l e r = = " D R O P D O W N " )   { 
 	 	 v C a l H e a d e r + = " < t d   a l i g n = ' c e n t e r ' > < s e l e c t   n a m e = \ " M o n t h S e l e c t o r \ "   o n C h a n g e = \ " j a v a s c r i p t : C a l . S w i t c h M t h ( t h i s . s e l e c t e d I n d e x ) ; R e n d e r C s s C a l ( ) ; \ " > \ n " ; 
 	 	 f o r   ( i = 0 ; i < 1 2 ; i + + )   { 
 	 	 	 i f   ( i = = C a l . M o n t h ) 
 	 	 	 	 S e l e c t S t r = " S e l e c t e d " ; 
 	 	 	 e l s e 
 	 	 	 	 S e l e c t S t r = " " ; 
 	 	 	         v C a l H e a d e r + = " < o p t i o n   " + S e l e c t S t r + "   v a l u e = " + i + " > " + M o n t h N a m e [ i ] + " < / o p t i o n > \ n " ; 
 	 	 } 
 	 	 v C a l H e a d e r + = " < / s e l e c t > < / t d > \ n " ; 
 	 	 / / Y e a r   s e l e c t o r 
 	 	 v C a l H e a d e r + = " < t d   a l i g n = ' c e n t e r ' > < s e l e c t   n a m e = \ " Y e a r S e l e c t o r \ "   s i z e = \ " 1 \ "   o n C h a n g e = \ " j a v a s c r i p t : C a l . S w i t c h Y e a r ( t h i s . v a l u e ) ; R e n d e r C s s C a l ( ) ; \ " > \ n " ; 
 	 	 f o r   ( i   =   1 9 5 0 ;   i   <   ( d t T o d a y . g e t F u l l Y e a r ( )   +   5 ) ; i + + ) 	 { 
 	 	 	 i f   ( i = = C a l . Y e a r ) 
 	 	 	 	 S e l e c t S t r = " S e l e c t e d " ; 
 	 	 	 e l s e 
 	 	 	 	 S e l e c t S t r = " " ; 	 
 	 	 	 v C a l H e a d e r + = " < o p t i o n   " + S e l e c t S t r + "   v a l u e = " + i + " > " + i + " < / o p t i o n > \ n " ; 
 	 	 } 
 	 	 v C a l H e a d e r + = " < / s e l e c t > < / t d > \ n " ; 
 	 	 c a l H e i g h t   + =   3 0 ; 
 	 } 
 	 / / * * * * * * * * * * * * * * * * * * E n d   M o n t h   a n d   Y e a r   s e l e c t o r   i n   d r o p d o w n   l i s t * * * * * * * * * * * * * * * * * * * * * 
 	 / / * * * * * * * * * * * * * * * * * * M o n t h   a n d   Y e a r   s e l e c t o r   i n   a r r o w * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
 	 e l s e   i f   ( C a l . S c r o l l e r = = " A R R O W " ) 	 
     { 	 
         i f   ( U s e I m a g e F i l e s ) 
         { 
     	 	 v C a l H e a d e r + = " < t d > < i m g   o n m o u s e d o w n = ' j a v a s c r i p t : C a l . D e c Y e a r ( ) ; R e n d e r C s s C a l ( ) ; '   s r c = ' s c r i p t s / d a t e t i m e / i m a g e s / c a l _ f a s t r e v e r s e . g i f '   w i d t h = ' 1 3 '   h e i g h t = ' 9 '   o n m o u s e o v e r = ' c h a n g e B o r d e r ( t h i s ,   0 ) '   o n m o u s e o u t = ' c h a n g e B o r d e r ( t h i s ,   1 ) '   s t y l e = ' b o r d e r : 1 p x   s o l i d   w h i t e ' > < / t d > \ n " ; / / Y e a r   s c r o l l e r   ( d e c r e a s e   1   y e a r ) 
     	 	 v C a l H e a d e r + = " < t d > < i m g   o n m o u s e d o w n = ' j a v a s c r i p t : C a l . D e c M o n t h ( ) ; R e n d e r C s s C a l ( ) ; '   s r c = ' s c r i p t s / d a t e t i m e / i m a g e s / c a l _ r e v e r s e . g i f '   w i d t h = ' 1 3 '   h e i g h t = ' 9 '   o n m o u s e o v e r = ' c h a n g e B o r d e r ( t h i s ,   0 ) '   o n m o u s e o u t = ' c h a n g e B o r d e r ( t h i s ,   1 ) '   s t y l e = ' b o r d e r : 1 p x   s o l i d   w h i t e ' > < / t d > \ n " ; / / M o n t h   s c r o l l e r   ( d e c r e a s e   1   m o n t h ) 
     	 	 v C a l H e a d e r + = " < t d   w i d t h = ' 7 0 % '   c l a s s = ' c a l R ' > < f o n t   c o l o r = ' " + Y r S e l C o l o r + " ' > " + C a l . G e t M o n t h N a m e ( S h o w L o n g M o n t h ) + "   " + C a l . Y e a r + " < / f o n t > < / t d > \ n " / / M o n t h   a n d   Y e a r 
     	 	 v C a l H e a d e r + = " < t d > < i m g   o n m o u s e d o w n = ' j a v a s c r i p t : C a l . I n c M o n t h ( ) ; R e n d e r C s s C a l ( ) ; '   s r c = ' s c r i p t s / d a t e t i m e / i m a g e s / c a l _ f o r w a r d . g i f '   w i d t h = ' 1 3 '   h e i g h t = ' 9 '   o n m o u s e o v e r = ' c h a n g e B o r d e r ( t h i s ,   0 ) '   o n m o u s e o u t = ' c h a n g e B o r d e r ( t h i s ,   1 ) '   s t y l e = ' b o r d e r : 1 p x   s o l i d   w h i t e ' > < / t d > \ n " ; / / M o n t h   s c r o l l e r   ( i n c r e a s e   1   m o n t h ) 
     	 	 v C a l H e a d e r + = " < t d > < i m g   o n m o u s e d o w n = ' j a v a s c r i p t : C a l . I n c Y e a r ( ) ; R e n d e r C s s C a l ( ) ; '   s r c = ' s c r i p t s / d a t e t i m e / i m a g e s / c a l _ f a s t f o r w a r d . g i f '   w i d t h = ' 1 3 '   h e i g h t = ' 9 '   o n m o u s e o v e r = ' c h a n g e B o r d e r ( t h i s ,   0 ) '   o n m o u s e o u t = ' c h a n g e B o r d e r ( t h i s ,   1 ) '   s t y l e = ' b o r d e r : 1 p x   s o l i d   w h i t e ' > < / t d > \ n " ; / / Y e a r   s c r o l l e r   ( i n c r e a s e   1   y e a r ) 
     	         c a l H e i g h t   + =   2 2 ; 
 	     } 
 	     e l s e 
 	     { 
 	     	 v C a l H e a d e r + = " < t d > < s p a n   i d = ' d e c _ y e a r '   t i t l e = ' r e v e r s e   y e a r '   o n m o u s e d o w n = ' j a v a s c r i p t : C a l . D e c Y e a r ( ) ; R e n d e r C s s C a l ( ) ; '   o n m o u s e o v e r = ' c h a n g e B o r d e r ( t h i s ,   0 ) '   o n m o u s e o u t = ' c h a n g e B o r d e r ( t h i s ,   1 ) '   s t y l e = ' b o r d e r : 1 p x   s o l i d   w h i t e ;   c o l o r : " + Y r S e l C o l o r + " ' > - < / s p a n > < / t d > " ; / / Y e a r   s c r o l l e r   ( d e c r e a s e   1   y e a r ) 
 	     	 v C a l H e a d e r + = " < t d > < s p a n   i d = ' d e c _ m o n t h '   t i t l e = ' r e v e r s e   m o n t h '   o n m o u s e d o w n = ' j a v a s c r i p t : C a l . D e c M o n t h ( ) ; R e n d e r C s s C a l ( ) ; '   o n m o u s e o v e r = ' c h a n g e B o r d e r ( t h i s ,   0 ) '   o n m o u s e o u t = ' c h a n g e B o r d e r ( t h i s ,   1 ) '   s t y l e = ' b o r d e r : 1 p x   s o l i d   w h i t e ' > & l t ; < / s p a n > < / t d > \ n " ; / / M o n t h   s c r o l l e r   ( d e c r e a s e   1   m o n t h ) 
     	 	 v C a l H e a d e r + = " < t d   w i d t h = ' 7 0 % '   c l a s s = ' c a l R ' > < f o n t   c o l o r = ' " + Y r S e l C o l o r + " ' > " + C a l . G e t M o n t h N a m e ( S h o w L o n g M o n t h ) + "   " + C a l . Y e a r + " < / f o n t > < / t d > \ n " / / M o n t h   a n d   Y e a r 
     	 	 v C a l H e a d e r + = " < t d > < s p a n   i d = ' i n c _ m o n t h '   t i t l e = ' f o r w a r d   m o n t h '   o n m o u s e d o w n = ' j a v a s c r i p t : C a l . I n c M o n t h ( ) ; R e n d e r C s s C a l ( ) ; '   o n m o u s e o v e r = ' c h a n g e B o r d e r ( t h i s ,   0 ) '   o n m o u s e o u t = ' c h a n g e B o r d e r ( t h i s ,   1 ) '   s t y l e = ' b o r d e r : 1 p x   s o l i d   w h i t e ' > & g t ; < / s p a n > < / t d > \ n " ; / / M o n t h   s c r o l l e r   ( i n c r e a s e   1   m o n t h ) 
     	 	 v C a l H e a d e r + = " < t d > < s p a n   i d = ' i n c _ y e a r '   t i t l e = ' f o r w a r d   y e a r '   o n m o u s e d o w n = ' j a v a s c r i p t : C a l . I n c Y e a r ( ) ; R e n d e r C s s C a l ( ) ; '     o n m o u s e o v e r = ' c h a n g e B o r d e r ( t h i s ,   0 ) '   o n m o u s e o u t = ' c h a n g e B o r d e r ( t h i s ,   1 ) '   s t y l e = ' b o r d e r : 1 p x   s o l i d   w h i t e ;   c o l o r : " + Y r S e l C o l o r + " ' > + < / s p a n > < / t d > \ n " ; / / Y e a r   s c r o l l e r   ( i n c r e a s e   1   y e a r ) 
     	         c a l H e i g h t   + =   2 2 ; 
 	     } 
 	 } 
 	 v C a l H e a d e r + = " < / t r > \ n < / t a b l e > \ n < / t d > \ n < / t r > \ n " 
     / / * * * * * * * * * * * * * * * * * * E n d   M o n t h   a n d   Y e a r   s e l e c t o r   i n   a r r o w * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
 	 / / C a l e n d a r   h e a d e r   s h o w s   M o n t h   a n d   Y e a r 
 	 i f   ( ( S h o w M o n t h Y e a r ) & & ( C a l . S c r o l l e r = = " D R O P D O W N " ) )   { 
 	 	 v C a l H e a d e r + = " < t r > < t d   c o l s p a n = ' 7 '   c l a s s = ' c a l R ' > \ n < f o n t   c o l o r = ' " + M o n t h Y e a r C o l o r + " ' > " + C a l . G e t M o n t h N a m e ( S h o w L o n g M o n t h ) + "   " + C a l . Y e a r + " < / f o n t > \ n < / t d > < / t r > \ n " ; 
 	         c a l H e i g h t   + =   1 9 ; 
 	 } 
 	 / / W e e k   d a y   h e a d e r 
 	 v C a l H e a d e r + = " < t r   b g c o l o r = " + W e e k H e a d C o l o r + " > \ n " ; 
 	 v a r   W e e k D a y N a m e = n e w   A r r a y ( ) ; / / A d d e d   v e r s i o n   1 . 7 
 	 i f   ( M o n d a y F i r s t D a y = = t r u e ) 
 	 	 W e e k D a y N a m e = W e e k D a y N a m e 2 ; 
 	 e l s e 
 	 	 W e e k D a y N a m e = W e e k D a y N a m e 1 ; 
 	 f o r   ( i = 0 ; i < 7 ; i + + )   { 
 	 	 v C a l H e a d e r + = " < t d   w i d t h = ' " + C e l l W i d t h + " '   c l a s s = ' c a l T D ' > < f o n t   c o l o r = ' w h i t e ' > " + W e e k D a y N a m e [ i ] . s u b s t r ( 0 , W e e k C h a r ) + " < / f o n t > < / t d > \ n " ; 
 	 } 
 	 c a l H e i g h t   + =   1 9 ; 
 	 v C a l H e a d e r + = " < / t r > \ n " ; 	 
 	 / / C a l e n d a r   d e t a i l 
 	 C a l D a t e = n e w   D a t e ( C a l . Y e a r , C a l . M o n t h ) ; 
 	 C a l D a t e . s e t D a t e ( 1 ) ; 
 	 v F i r s t D a y = C a l D a t e . g e t D a y ( ) ; 
 	 / / A d d e d   v e r s i o n   1 . 7 
 	 i f   ( M o n d a y F i r s t D a y = = t r u e )   { 
 	 	 v F i r s t D a y - = 1 ; 
 	 	 i f   ( v F i r s t D a y = = - 1 ) 
 	 	 	 v F i r s t D a y = 6 ; 
 	 } 
 	 / / A d d e d   v e r s i o n   1 . 7 
 	 v C a l D a t a = " < t r > " ; 
 	 c a l H e i g h t   + =   1 9 ; 
 	 f o r   ( i = 0 ; i < v F i r s t D a y ; i + + )   { 
 	 	 v C a l D a t a = v C a l D a t a + G e n C e l l ( ) ; 
 	 	 v D a y C o u n t = v D a y C o u n t + 1 ; 
 	 } 
 	 / / A d d e d   v e r s i o n   1 . 7 
 	 f o r   ( j = 1 ; j < = C a l . G e t M o n D a y s ( ) ; j + + )   { 
 	 	 v a r   s t r C e l l ; 
 	 	 i f ( ( v D a y C o u n t % 7 = = 0 ) & & ( j   >   1 ) )   { 
 	 	 	 v C a l D a t a = v C a l D a t a + " \ n < t r > " ; 
 	 	 } 
 	 	 v D a y C o u n t = v D a y C o u n t + 1 ; 
 	 	 i f   ( ( j = = d t T o d a y . g e t D a t e ( ) ) & & ( C a l . M o n t h = = d t T o d a y . g e t M o n t h ( ) ) & & ( C a l . Y e a r = = d t T o d a y . g e t F u l l Y e a r ( ) ) ) 
 	 	 	 s t r C e l l = G e n C e l l ( j , t r u e , T o d a y C o l o r ) ; / / H i g h l i g h t   t o d a y ' s   d a t e 
 	 	 e l s e   { 
 	 	 	 i f   ( ( j = = s e l D a t e . g e t D a t e ( ) ) & & ( C a l . M o n t h = = s e l D a t e . g e t M o n t h ( ) ) & & ( C a l . Y e a r = = s e l D a t e . g e t F u l l Y e a r ( ) ) )   {   / / m o d i f i e d   v e r s i o n   1 . 7 
 	 	 	 	 s t r C e l l = G e n C e l l ( j , t r u e , S e l D a t e C o l o r ) ; 
 	 	 	 } 
 	 	 	 e l s e   { 	 
 	 	 	 	 i f   ( M o n d a y F i r s t D a y = = t r u e )   { 
 	 	 	 	 	 i f   ( v D a y C o u n t % 7 = = 0 ) 
 	 	 	 	 	 	 s t r C e l l = G e n C e l l ( j , f a l s e , S u n d a y C o l o r ) ; 
 	 	 	 	 	 e l s e   i f   ( ( v D a y C o u n t + 1 ) % 7 = = 0 ) 
 	 	 	 	 	 	 s t r C e l l = G e n C e l l ( j , f a l s e , S a t u r d a y C o l o r ) ; 
 	 	 	 	 	 e l s e 
 	 	 	 	 	 	 s t r C e l l = G e n C e l l ( j , n u l l , W e e k D a y C o l o r ) ; 	 	 	 	 	 
 	 	 	 	 }   
 	 	 	 	 e l s e   { 
 	 	 	 	 	 i f   ( v D a y C o u n t % 7 = = 0 ) 
 	 	 	 	 	 	 s t r C e l l = G e n C e l l ( j , f a l s e , S a t u r d a y C o l o r ) ; 
 	 	 	 	 	 e l s e   i f   ( ( v D a y C o u n t + 6 ) % 7 = = 0 ) 
 	 	 	 	 	 	 s t r C e l l = G e n C e l l ( j , f a l s e , S u n d a y C o l o r ) ; 
 	 	 	 	 	 e l s e 
 	 	 	 	 	 	 s t r C e l l = G e n C e l l ( j , n u l l , W e e k D a y C o l o r ) ; 
 	 	 	 	 } 
 	 	 	 } 	 	 
 	 	 } 	 	 	 	 	 	 
 	 	 v C a l D a t a = v C a l D a t a + s t r C e l l ; 
 
 	 	 i f ( ( v D a y C o u n t % 7 = = 0 ) & & ( j < C a l . G e t M o n D a y s ( ) ) )   { 
 	 	 	 v C a l D a t a = v C a l D a t a + " \ n < / t r > " ; 
 	 	 	 c a l H e i g h t   + =   1 9 ; 
 	 	 } 
 	 } 
 	 / /   f i n i s h   t h e   t a b l e   p r o p e r 
 	 i f ( ! ( v D a y C o u n t % 7 )   = =   0 )   { 
 	 	 w h i l e ( ! ( v D a y C o u n t   %   7 )   = =   0 )   { 
 	 	 	 v C a l D a t a = v C a l D a t a + G e n C e l l ( ) ; 
 	 	 	 v D a y C o u n t = v D a y C o u n t + 1 ; 
 	 	 } 
 	 } 
 	 v C a l D a t a = v C a l D a t a + " \ n < / t r > " ; 
 	 
 	 / / T i m e   p i c k e r 
 	 i f   ( C a l . S h o w T i m e )   
 	 { 
 	 	 v a r   s h o w H o u r ; 
 	 	 v a r   S h o w A r r o w s = f a l s e ; 
 	 	 v a r   H o u r C e l l W i d t h = " 3 5 p x " ;   / / c e l l   w i d t h   w i t h   s e c o n d s . 
 	 	 s h o w H o u r = C a l . g e t S h o w H o u r ( ) ; 
 	 	 
 	 	 i f   ( C a l . S h o w S e c o n d s = = f a l s e   & &   T i m e M o d e = = 2 4   )   
                 { 
 	 	       S h o w A r r o w s = t r u e ; 
 	 	       H o u r C e l l W i d t h = " 1 0 p x " ; 
 	 	 } 
 	 	 
 	 	 v C a l T i m e = " \ n < t r > \ n < t d   c o l s p a n = ' 7 '   a l i g n = ' c e n t e r ' > < c e n t e r > \ n < t a b l e   b o r d e r = ' 0 '   w i d t h = ' 1 9 9 p x '   c e l l p a d d i n g = ' 0 '   c e l l s p a c i n g = ' 2 ' > \ n < t r > \ n < t d   h e i g h t = ' 5 p x '   w i d t h = ' " + H o u r C e l l W i d t h + " ' > & n b s p ; < / t d > \ n " ; 
 	 	 
 	 	 i f   ( S h o w A r r o w s   & &   U s e I m a g e F i l e s )   
 	 	 {       
                         v C a l T i m e + = " < t d   a l i g n = ' c e n t e r ' > < t a b l e   c e l l s p a c i n g = ' 0 '   c e l l p a d d i n g = ' 0 '   s t y l e = ' l i n e - h e i g h t : 0 p t ' > < t r > < t d > < i m g   o n m o u s e d o w n = ' j a v a s c r i p t : C a l . S e t H o u r ( C a l . H o u r s   +   1 ) ; R e n d e r C s s C a l ( ) ; '   s r c = ' s c r i p t s / d a t e t i m e / i m a g e s / c a l _ p l u s . g i f '   w i d t h = ' 1 3 '   h e i g h t = ' 9 '   o n m o u s e o v e r = ' c h a n g e B o r d e r ( t h i s ,   0 ) '   o n m o u s e o u t = ' c h a n g e B o r d e r ( t h i s ,   1 ) '   s t y l e = ' b o r d e r : 1 p x   s o l i d   w h i t e ' > < / t d > < / t r > < t r > < t d > < i m g   o n m o u s e d o w n = ' j a v a s c r i p t : C a l . S e t H o u r ( C a l . H o u r s   -   1 ) ; R e n d e r C s s C a l ( ) ; '   s r c = ' s c r i p t s / d a t e t i m e / i m a g e s / c a l _ m i n u s . g i f '   w i d t h = ' 1 3 '   h e i g h t = ' 9 '   o n m o u s e o v e r = ' c h a n g e B o r d e r ( t h i s ,   0 ) '   o n m o u s e o u t = ' c h a n g e B o r d e r ( t h i s ,   1 ) '   s t y l e = ' b o r d e r : 1 p x   s o l i d   w h i t e ' > < / t d > < / t r > < / t a b l e > < / t d > \ n " ;   
 	 	 } 
 	 	 
 	 	 v C a l T i m e + = " < t d   a l i g n = ' c e n t e r '   w i d t h = ' 2 2 p x ' > < i n p u t   t y p e = ' t e x t '   n a m e = ' h o u r '   m a x l e n g t h = 2   s i z e = 1   s t y l e = \ " W I D T H :   2 2 p x \ "   v a l u e = " + s h o w H o u r + "   o n C h a n g e = \ " j a v a s c r i p t : C a l . S e t H o u r ( t h i s . v a l u e ) \ " > " ; 
 	 	 v C a l T i m e + = " < / t d > < t d   a l i g n = ' c e n t e r ' > : < / t d > < t d   a l i g n = ' c e n t e r '   w i d t h = ' 2 2 p x ' > " ; 
 	 	 v C a l T i m e + = " < i n p u t   t y p e = ' t e x t '   n a m e = ' m i n u t e '   m a x l e n g t h = 2   s i z e = 1   s t y l e = \ " W I D T H :   2 2 p x \ "   v a l u e = " + C a l . M i n u t e s + "   o n C h a n g e = \ " j a v a s c r i p t : C a l . S e t M i n u t e ( t h i s . v a l u e ) \ " > " ; 
 	 	 
 	 	 i f   ( C a l . S h o w S e c o n d s )   { 
 	 	 	 v C a l T i m e + = " < / t d > < t d   a l i g n = ' c e n t e r ' > : < / t d > < t d   a l i g n = ' c e n t e r '   w i d t h = ' 2 2 p x ' > " ; 
 	 	 	 v C a l T i m e + = " < i n p u t   t y p e = ' t e x t '   n a m e = ' s e c o n d '   m a x l e n g t h = 2   s i z e = 1   s t y l e = \ " W I D T H :   2 2 p x \ "   v a l u e = " + C a l . S e c o n d s + "   o n C h a n g e = \ " j a v a s c r i p t : C a l . S e t S e c o n d ( p a r s e I n t ( t h i s . v a l u e , 1 0 ) ) \ " > " ; 
 	 	 } 
 	 	 i f   ( T i m e M o d e = = 1 2 )   { 
 	 	 	 v a r   S e l e c t A m   = ( C a l . A M o r P M = = " A M " ) ?   " S e l e c t e d " : " " ; 
 	 	 	 v a r   S e l e c t P m   = ( C a l . A M o r P M = = " P M " ) ?   " S e l e c t e d " : " " ; 
                         
                         v C a l T i m e + = " < / t d > < t d > " ; 
 	 	 	 v C a l T i m e + = " < s e l e c t   n a m e = \ " a m p m \ "   o n C h a n g e = \ " j a v a s c r i p t : C a l . S e t A m P m ( t h i s . o p t i o n s [ t h i s . s e l e c t e d I n d e x ] . v a l u e ) ; \ " > \ n " ; 
 	 	 	 v C a l T i m e + = " < o p t i o n   " + S e l e c t A m + "   v a l u e = \ " A M \ " > A M < / o p t i o n > " ; 
 	 	 	 v C a l T i m e + = " < o p t i o n   " + S e l e c t P m + "   v a l u e = \ " P M \ " > P M < o p t i o n > " ; 
 	 	 	 v C a l T i m e + = " < / s e l e c t > " ; 
 	 	 } 
 	 	 i f   ( S h o w A r r o w s   & &   U s e I m a g e F i l e s )   { 
 	 	       v C a l T i m e + = " < / t d > \ n < t d   a l i g n = ' c e n t e r ' > < t a b l e   c e l l s p a c i n g = ' 0 '   c e l l p a d d i n g = ' 0 '   s t y l e = ' l i n e - h e i g h t : 0 p t ' > < t r > < t d > < i m g   o n m o u s e d o w n = ' j a v a s c r i p t : C a l . S e t M i n u t e ( p a r s e I n t ( C a l . M i n u t e s , 1 0 )   +   1 ) ; R e n d e r C s s C a l ( ) ; '   s r c = ' s c r i p t s / d a t e t i m e / i m a g e s / c a l _ p l u s . g i f '   w i d t h = ' 1 3 p x '   h e i g h t = ' 9 p x '   o n m o u s e o v e r = ' c h a n g e B o r d e r ( t h i s ,   0 ) '   o n m o u s e o u t = ' c h a n g e B o r d e r ( t h i s ,   1 ) '   s t y l e = ' b o r d e r : 1 p x   s o l i d   w h i t e ' > < / t d > < / t r > < t r > < t d > < i m g   o n m o u s e d o w n = ' j a v a s c r i p t : C a l . S e t M i n u t e ( p a r s e I n t ( C a l . M i n u t e s , 1 0 )   -   1 ) ; R e n d e r C s s C a l ( ) ; '   s r c = ' s c r i p t s / d a t e t i m e / i m a g e s / c a l _ m i n u s . g i f '   w i d t h = ' 1 3 p x '   h e i g h t = ' 9 p x '   o n m o u s e o v e r = ' c h a n g e B o r d e r ( t h i s ,   0 ) '   o n m o u s e o u t = ' c h a n g e B o r d e r ( t h i s ,   1 ) '   s t y l e = ' b o r d e r : 1 p x   s o l i d   w h i t e ' > < / t d > < / t r > < / t a b l e > " ;   
 	 	 } 
 	 	 v C a l T i m e + = " < / t d > \ n < t d   a l i g n = ' r i g h t '   v a l i g n = ' b o t t o m '   w i d t h = ' " + H o u r C e l l W i d t h + " ' > " ; 
 	 	 
 	 } 
 	 e l s e 
 	 	 { v C a l T i m e + = " \ n < t r > \ n < t d   c o l s p a n = ' 7 '   a l i g n = ' r i g h t ' > " ; } 
 	 i f   ( U s e I m a g e F i l e s ) 
 	 { 
                 v C a l T i m e + = " < i m g   o n m o u s e d o w n = ' j a v a s c r i p t : c l o s e w i n ( \ " "   +   C a l . C t r l   +   " \ " ) ; '   s r c = ' s c r i p t s / d a t e t i m e / i m a g e s / c a l _ c l o s e . g i f '   w i d t h = ' 1 6 '   h e i g h t = ' 1 4 '   o n m o u s e o v e r = ' c h a n g e B o r d e r ( t h i s ,   0 ) '   o n m o u s e o u t = ' c h a n g e B o r d e r ( t h i s ,   1 ) '   s t y l e = ' b o r d e r : 1 p x   s o l i d   w h i t e ' > < / t d > " ; 
         } 
         e l s e 
         { 
                 v C a l T i m e + = " < s p a n   i d = ' c l o s e _ c a l '   t i t l e = ' c l o s e '   o n m o u s e d o w n = ' j a v a s c r i p t : c l o s e w i n ( \ " "   +   C a l . C t r l   +   " \ " ) ; '   o n m o u s e o v e r = ' c h a n g e B o r d e r ( t h i s ,   0 ) '   o n m o u s e o u t = ' c h a n g e B o r d e r ( t h i s ,   1 ) '   s t y l e = ' b o r d e r : 1 p x   s o l i d   w h i t e ;   f o n t - f a m i l y :   A r i a l ; f o n t - s i z e :   1 0 p t ; ' > x < / s p a n > < / t d > " ; 
         } 
 
         v C a l T i m e + = " < / t r > \ n < / t a b l e > < / c e n t e r > \ n < / t d > \ n < / t r > " ; 
         c a l H e i g h t   + =   3 1 ; 	 
 	 v C a l T i m e + = " \ n < / t a b l e > \ n < / s p a n > " ; 
         	 
         / / e n d   t i m e   p i c k e r 
         v a r   f u n c C a l b a c k = " f u n c t i o n   c a l l b a c k ( i d ,   d a t u m )   { \ n " ; 
         f u n c C a l b a c k + = "   v a r   C a l I d   =   d o c u m e n t . g e t E l e m e n t B y I d ( i d ) ;   C a l I d . v a l u e = d a t u m ; \ n " ; 
         f u n c C a l b a c k + = "   i f   ( C a l . S h o w T i m e )   { \ n " ; 
         f u n c C a l b a c k + = "   C a l I d . v a l u e + = '   ' + C a l . g e t S h o w H o u r ( ) + ' : ' + C a l . M i n u t e s ; \ n " ; 
         f u n c C a l b a c k + = "   i f   ( C a l . S h o w S e c o n d s ) \ n     C a l I d . v a l u e + = ' : ' + C a l . S e c o n d s ; \ n " ; 
         f u n c C a l b a c k + = "   i f   ( T i m e M o d e = = 1 2 ) \ n     C a l I d . v a l u e + = '   ' + C a l . g e t S h o w A M o r P M ( ) ; \ n " ; 	 
         f u n c C a l b a c k + = " } \ n   C a l I d . f o c u s ( ) ;   \ n   w i n C a l . s t y l e . v i s i b i l i t y = ' h i d d e n ' ; \ n } \ n " ; 
 	 
 	 / /   d e t e r m i n e s   i f   t h e r e   i s   e n o u g h   s p a c e   t o   o p e n   t h e   c a l   a b o v e   t h e   p o s i t i o n   w h e r e   i t   i s   c a l l e d 
 	 i f   ( y p o s   >   c a l H e i g h t )   { 
 	       y p o s   =   y p o s   -   c a l H e i g h t ;   
 	 } 
 	 i f   ( w i n C a l   = =   u n d e f i n e d )   { 
 	       v a r   h e a d I D   =   d o c u m e n t . g e t E l e m e n t s B y T a g N a m e ( " h e a d " ) [ 0 ] ; 
 	       
 	       / /   a d d   j a v a s c r i p t   f u n c t i o n   t o   t h e   s p a n   c a l 
               v a r   e   =   d o c u m e n t . c r e a t e E l e m e n t ( " s c r i p t " ) ; 
               e . t y p e   =   " t e x t / j a v a s c r i p t " ; 
               e . l a n g u a g e   =   " j a v a s c r i p t " ; 
               e . t e x t   =   f u n c C a l b a c k ; 
               h e a d I D . a p p e n d C h i l d ( e ) ; 
 	       
 	       / /   a d d   s t y l e s h e e t   t o   t h e   s p a n   c a l 
 	       v a r   c s s S t r   =   " . c a l T D   { f o n t - f a m i l y :   v e r d a n a ;   f o n t - s i z e :   1 2 p x ;   t e x t - a l i g n :   c e n t e r ; } \ n " ; 
 	       c s s S t r + =   " . c a l R   { f o n t - f a m i l y :   v e r d a n a ;   f o n t - s i z e :   1 2 p x ;   t e x t - a l i g n :   c e n t e r ;   f o n t - w e i g h t :   b o l d ;   c o l o r :   r e d ; } " 
 	       v a r   s t y l e   =   d o c u m e n t . c r e a t e E l e m e n t ( " s t y l e " ) ; 
               s t y l e . t y p e   =   " t e x t / c s s " ; 
               s t y l e . r e l   =   " s t y l e s h e e t " ; 
               i f ( s t y l e . s t y l e S h e e t )   {   / /   I E 
                     s t y l e . s t y l e S h e e t . c s s T e x t   =   c s s S t r ; 
                 }   
 	       e l s e   {   / /   w 3 c 
                     v a r   c s s T e x t   =   d o c u m e n t . c r e a t e T e x t N o d e ( c s s S t r ) ; 
                     s t y l e . a p p e n d C h i l d ( c s s T e x t ) ; 
 	 	 } 
               h e a d I D . a p p e n d C h i l d ( s t y l e ) ; 
 	       
 	       / /   c r e a t e   t h e   o u t e r   f r a m e   t h a t   a l l o w s   t h e   c a l .   t o   b e   m o v e d 
 	       v a r   s p a n   =   d o c u m e n t . c r e a t e E l e m e n t ( " s p a n " ) ; 
               s p a n . i d   =   c a l S p a n I D ; 
 
 	       w i t h   ( s p a n . s t y l e )   { p o s i t i o n   =   " a b s o l u t e " ;   l e f t   =   ( x p o s + 8 ) + ' p x ' ;   t o p   =   ( y p o s - 8 ) + ' p x ' ;   w i d t h   =   C a l W i d t h ;   b o r d e r   =   " s o l i d   2 p t   "   +   S p a n B o r d e r C o l o r ;   p a d d i n g   =   " 0 p t " ;   c u r s o r   =   " m o v e " ;   b a c k g r o u n d C o l o r   =   S p a n B g C o l o r ;   z I n d e x   =   1 0 0 ; } 
 
               d o c u m e n t . b o d y . a p p e n d C h i l d ( s p a n ) 
               w i n C a l = d o c u m e n t . g e t E l e m e n t B y I d ( c a l S p a n I D ) ; 
         } 
         e l s e   { 
 	     w i n C a l . s t y l e . v i s i b i l i t y   =   " v i s i b l e " ; 
 	     w i n C a l . s t y l e . H e i g h t   =   c a l H e i g h t ; 
 
 	     / /   s e t   t h e   p o s i t i o n   f o r   a   n e w   c a l e n d a r   o n l y 
 	     i f ( b N e w C a l = = t r u e ) { 
 	           w i n C a l . s t y l e . l e f t   =   ( x p o s + 8 ) + ' p x ' ; 
 	           w i n C a l . s t y l e . t o p   =   ( y p o s - 8 ) + ' p x ' ; 
 	       } 
 	 } 
 	 w i n C a l . i n n e r H T M L = w i n C a l D a t a   +   v C a l H e a d e r   +   v C a l D a t a   +   v C a l T i m e ; 
 	 r e t u r n   t r u e ; 
 } 
 
 f u n c t i o n   G e n C e l l ( p V a l u e , p H i g h L i g h t , p C o l o r )   {   / / G e n e r a t e   t a b l e   c e l l   w i t h   v a l u e 
 	 v a r   P V a l u e ; 
 	 v a r   P C e l l S t r ; 
 	 v a r   v C o l o r ; 
 	 v a r   v H L s t r 1 ; / / H i g h L i g h t   s t r i n g 
 	 v a r   v H l s t r 2 ; 
 	 v a r   v T i m e S t r ; 
 	 
 	 i f   ( p V a l u e = = n u l l ) 
 	 	 P V a l u e = " " ; 
 	 e l s e 
 	 	 P V a l u e = p V a l u e ; 
 	 
 	 i f   ( p C o l o r ! = n u l l ) 
 	 	 v C o l o r = " b g c o l o r = \ " " + p C o l o r + " \ " " ; 
 	 e l s e 
 	 	 v C o l o r = C a l B g C o l o r ; 
 	         i f   ( ( p H i g h L i g h t ! = n u l l ) & & ( p H i g h L i g h t ) )   { 
 	 	       v H L s t r 1 = " < f o n t   c l a s s = ' c a l R ' > " ; v H L s t r 2 = " < / f o n t > " ; 
 	 	   } 
 	         e l s e   { 
 	 	       v H L s t r 1 = " " ; v H L s t r 2 = " " ; 
 	 	   } 
 	 i f   ( C a l . S h o w T i m e )   { 
 	 	 v T i m e S t r = '   ' + C a l . H o u r s + ' : ' + C a l . M i n u t e s ; 
 	 	 i f   ( C a l . S h o w S e c o n d s ) 
 	 	 	 v T i m e S t r + = ' : ' + C a l . S e c o n d s ; 
 	 	 i f   ( T i m e M o d e = = 1 2 ) 
 	 	 	 v T i m e S t r + = '   ' + C a l . A M o r P M ; 
 	 } 	 
 	 e l s e 
 	 	 v T i m e S t r = " " ; 	 	 
 	 i f   ( P V a l u e ! = " " ) 
 	 	 P C e l l S t r = " \ n < t d   " + v C o l o r + "   c l a s s = ' c a l T D '   s t y l e = ' c u r s o r :   p o i n t e r ; '   o n C l i c k = \ " j a v a s c r i p t : c a l l b a c k ( ' " + C a l . C t r l + " ' , ' " + C a l . F o r m a t D a t e ( P V a l u e ) + " ' ) ; \ " > " + v H L s t r 1 + P V a l u e + v H L s t r 2 + " < / t d > " ; 
 	 e l s e 
 	 	 P C e l l S t r = " \ n < t d   " + v C o l o r + "   c l a s s = ' c a l T D ' > & n b s p ; < / t d > " ; 
 	 r e t u r n   P C e l l S t r ; 
 } 
 
 f u n c t i o n   C a l e n d a r ( p D a t e , p C t r l )   { 
 	 / / P r o p e r t i e s 
 	 t h i s . D a t e = p D a t e . g e t D a t e ( ) ; / / s e l e c t e d   d a t e 
 	 t h i s . M o n t h = p D a t e . g e t M o n t h ( ) ; / / s e l e c t e d   m o n t h   n u m b e r 
 	 t h i s . Y e a r = p D a t e . g e t F u l l Y e a r ( ) ; / / s e l e c t e d   y e a r   i n   4   d i g i t s 
 	 t h i s . H o u r s = p D a t e . g e t H o u r s ( ) ; 
 	 
 	 i f   ( p D a t e . g e t M i n u t e s ( ) < 1 0 ) 
 	 	 t h i s . M i n u t e s = " 0 " + p D a t e . g e t M i n u t e s ( ) ; 
 	 e l s e 
 	 	 t h i s . M i n u t e s = p D a t e . g e t M i n u t e s ( ) ; 
 	 
 	 i f   ( p D a t e . g e t S e c o n d s ( ) < 1 0 ) 
 	 	 t h i s . S e c o n d s = " 0 " + p D a t e . g e t S e c o n d s ( ) ; 
 	 e l s e 	 	 
 	 	 t h i s . S e c o n d s = p D a t e . g e t S e c o n d s ( ) ; 
 	 	 
 	 t h i s . M y W i n d o w = w i n C a l ; 
 	 t h i s . C t r l = p C t r l ; 
 	 t h i s . F o r m a t = " d d M M y y y y " ; 
 	 t h i s . S e p a r a t o r = D a t e S e p a r a t o r ; 
 	 t h i s . S h o w T i m e = f a l s e ; 
 	 t h i s . S c r o l l e r = " D R O P D O W N " ; 
 	 i f   ( p D a t e . g e t H o u r s ( ) < 1 2 ) 
 	 	 t h i s . A M o r P M = " A M " ; 
 	 e l s e 
 	 	 t h i s . A M o r P M = " P M " ; 
 	 t h i s . S h o w S e c o n d s = t r u e ; 	 	 
 } 
 
 f u n c t i o n   G e t M o n t h I n d e x ( s h o r t M o n t h N a m e )   { 
 	 f o r   ( i = 0 ; i < 1 2 ; i + + )   { 
 	 	 i f   ( M o n t h N a m e [ i ] . s u b s t r i n g ( 0 , 3 ) . t o U p p e r C a s e ( ) = = s h o r t M o n t h N a m e . t o U p p e r C a s e ( ) )   
 	 	       { r e t u r n   i ; } 
 	 } 
 } 
 C a l e n d a r . p r o t o t y p e . G e t M o n t h I n d e x = G e t M o n t h I n d e x ; 
 
 f u n c t i o n   I n c Y e a r ( )   { 
 	 C a l . Y e a r + + ; } 
 	 C a l e n d a r . p r o t o t y p e . I n c Y e a r = I n c Y e a r ; 
 
 f u n c t i o n   D e c Y e a r ( )   { 
 	 C a l . Y e a r - - ; } 
 	 C a l e n d a r . p r o t o t y p e . D e c Y e a r = D e c Y e a r ; 
 
 f u n c t i o n   I n c M o n t h ( )   { 	 
 	 C a l . M o n t h + + ; 
 	 i f   ( C a l . M o n t h > = 1 2 )   { 
 	 	 C a l . M o n t h = 0 ; 
 	 	 C a l . I n c Y e a r ( ) ; 
 	 } 
 } 
 C a l e n d a r . p r o t o t y p e . I n c M o n t h = I n c M o n t h ; 
 
 f u n c t i o n   D e c M o n t h ( )   { 	 
 	 C a l . M o n t h - - ; 
 	 i f   ( C a l . M o n t h < 0 )   { 
 	 	 C a l . M o n t h = 1 1 ; 
 	 	 C a l . D e c Y e a r ( ) ; 
 	 } 
 } 
 C a l e n d a r . p r o t o t y p e . D e c M o n t h = D e c M o n t h ; 
 	 
 f u n c t i o n   S w i t c h M t h ( i n t M t h )   { 
 	 C a l . M o n t h = i n t M t h ; } 
 	 C a l e n d a r . p r o t o t y p e . S w i t c h M t h = S w i t c h M t h ; 
 
 f u n c t i o n   S w i t c h Y e a r ( i n t Y e a r )   { 
 	 C a l . Y e a r = i n t Y e a r ; } 
 	 C a l e n d a r . p r o t o t y p e . S w i t c h Y e a r = S w i t c h Y e a r ; 
 
 f u n c t i o n   S e t H o u r ( i n t H o u r )   { 	 
 	 v a r   M a x H o u r ; 
 	 v a r   M i n H o u r ; 
 	 i f   ( T i m e M o d e = = 2 4 )   { 
 	 	 M a x H o u r = 2 3 ; M i n H o u r = 0 } 
 	 e l s e   i f   ( T i m e M o d e = = 1 2 )   { 
 	 	 M a x H o u r = 1 2 ; M i n H o u r = 1 } 
 	 e l s e 
 	 	 a l e r t ( " T i m e M o d e   c a n   o n l y   b e   1 2   o r   2 4 " ) ; 	 	 
 	 v a r   H o u r E x p = n e w   R e g E x p ( " ^ \ \ d \ \ d " ) ; 
 	 v a r   S i n g l e D i g i t = n e w   R e g E x p ( " \ \ d " ) ; 
 	 
 	 i f   ( ( H o u r E x p . t e s t ( i n t H o u r )   | |   S i n g l e D i g i t . t e s t ( i n t H o u r ) )   & &   ( p a r s e I n t ( i n t H o u r , 1 0 ) > M a x H o u r ) )   { 
 	         i n t H o u r   =   M i n H o u r ; 
 	 } 
 	 e l s e   i f   ( ( H o u r E x p . t e s t ( i n t H o u r )   | |   S i n g l e D i g i t . t e s t ( i n t H o u r ) )   & &   ( p a r s e I n t ( i n t H o u r , 1 0 ) < M i n H o u r ) )   { 
 	 	 i n t H o u r   =   M a x H o u r ; 
 	 } 
 	 
 	 i f   ( S i n g l e D i g i t . t e s t ( i n t H o u r ) )   { 
 	 	 i n t H o u r = " 0 " + i n t H o u r + " " ; 	 
 	 } 
 	 
 	 i f   ( H o u r E x p . t e s t ( i n t H o u r )   & &   ( p a r s e I n t ( i n t H o u r , 1 0 ) < = M a x H o u r )   & &   ( p a r s e I n t ( i n t H o u r , 1 0 ) > = M i n H o u r ) )   { 	 
 	 	 i f   ( ( T i m e M o d e = = 1 2 )   & &   ( C a l . A M o r P M = = " P M " ) )   { 
 	 	 	 i f   ( p a r s e I n t ( i n t H o u r , 1 0 ) = = 1 2 ) 
 	 	 	 	 C a l . H o u r s = 1 2 ; 
 	 	 	 e l s e 	 
 	 	 	 	 C a l . H o u r s = p a r s e I n t ( i n t H o u r , 1 0 ) + 1 2 ; 
 	 	 } 	 
 	 	 e l s e   i f   ( ( T i m e M o d e = = 1 2 )   & &   ( C a l . A M o r P M = = " A M " ) )   { 
 	 	 	 i f   ( i n t H o u r = = 1 2 ) 
 	 	 	 	 i n t H o u r - = 1 2 ; 
 	 	 	 C a l . H o u r s = p a r s e I n t ( i n t H o u r , 1 0 ) ; 
 	 	 } 
 	 	 e l s e   i f   ( T i m e M o d e = = 2 4 ) 
 	 	 	 C a l . H o u r s = p a r s e I n t ( i n t H o u r , 1 0 ) ; 	 
 	 } 
 } 
 C a l e n d a r . p r o t o t y p e . S e t H o u r = S e t H o u r ; 
 
 f u n c t i o n   S e t M i n u t e ( i n t M i n )   { 
 	 v a r   M a x M i n = 5 9 ; 
 	 v a r   M i n M i n = 0 ; 
 	 v a r   S i n g l e D i g i t = n e w   R e g E x p ( " \ \ d " ) ; 
 	 v a r   S i n g l e D i g i t 2 = n e w   R e g E x p ( " ^ \ \ d { 1 } $ " ) ; 
 	 v a r   M i n E x p = n e w   R e g E x p ( " ^ \ \ d { 2 } $ " ) ; 
 	 
 	 i f   ( ( M i n E x p . t e s t ( i n t M i n )   | |   S i n g l e D i g i t . t e s t ( i n t M i n ) )   & &   ( p a r s e I n t ( i n t M i n , 1 0 ) > M a x M i n ) )   { 
 	 	 i n t M i n   =   M i n M i n ; 
 	 } 
 	 e l s e   i f   ( ( M i n E x p . t e s t ( i n t M i n )   | |   S i n g l e D i g i t . t e s t ( i n t M i n ) )   & &   ( p a r s e I n t ( i n t M i n , 1 0 ) < M i n M i n ) ) 	 { 
 	 	 i n t M i n   =   M a x M i n ; 
 	 } 
 	 v a r   s t r M i n   =   i n t M i n   +   " " ; 
 	 i f   ( S i n g l e D i g i t 2 . t e s t ( i n t M i n ) )   { 
 	 	 s t r M i n = " 0 " + s t r M i n + " " ; 
 	 }   
 	 i f   ( ( M i n E x p . t e s t ( i n t M i n )   | |   S i n g l e D i g i t . t e s t ( i n t M i n ) )   
 	   & &   ( p a r s e I n t ( i n t M i n , 1 0 ) < = 5 9 )   & &   ( p a r s e I n t ( i n t M i n , 1 0 ) > = 0 ) )   { 
 	   	 C a l . M i n u t e s = s t r M i n ; 
 	 } 
 } 
 C a l e n d a r . p r o t o t y p e . S e t M i n u t e = S e t M i n u t e ; 
 
 f u n c t i o n   S e t S e c o n d ( i n t S e c )   { 	 
 	 v a r   S e c E x p = n e w   R e g E x p ( " ^ \ \ d \ \ d $ " ) ; 
 	 i f   ( S e c E x p . t e s t ( i n t S e c )   & &   ( i n t S e c < 6 0 ) ) 
 	 	 C a l . S e c o n d s = i n t S e c ; 
 } 
 C a l e n d a r . p r o t o t y p e . S e t S e c o n d = S e t S e c o n d ; 
 
 f u n c t i o n   S e t A m P m ( p v a l u e )   { 
 	 t h i s . A M o r P M = p v a l u e ; 
 	 i f   ( p v a l u e = = " P M " )   { 
 	 	 t h i s . H o u r s = ( p a r s e I n t ( t h i s . H o u r s , 1 0 ) ) + 1 2 ; 
 	 	 i f   ( t h i s . H o u r s = = 2 4 ) 
 	 	 	 t h i s . H o u r s = 1 2 ; 
 	 } 	 
 	 e l s e   i f   ( p v a l u e = = " A M " ) 
 	 	 t h i s . H o u r s - = 1 2 ; 	 
 } 
 C a l e n d a r . p r o t o t y p e . S e t A m P m = S e t A m P m ; 
 
 f u n c t i o n   g e t S h o w H o u r ( )   { 
 	 v a r   f i n a l H o u r ; 
         i f   ( T i m e M o d e = = 1 2 )   { 
         	 i f   ( p a r s e I n t ( t h i s . H o u r s , 1 0 ) = = 0 )   { 
 	 	 	 t h i s . A M o r P M = " A M " ; 
 	 	 	 f i n a l H o u r = p a r s e I n t ( t h i s . H o u r s , 1 0 ) + 1 2 ; 	 
 	 	 } 
 	 	 e l s e   i f   ( p a r s e I n t ( t h i s . H o u r s , 1 0 ) = = 1 2 )   { 
 	 	 	 t h i s . A M o r P M = " P M " ; 
 	 	 	 f i n a l H o u r = 1 2 ; 
 	 	 } 	 	 
 	 	 e l s e   i f   ( t h i s . H o u r s > 1 2 ) 	 { 
 	 	 	 t h i s . A M o r P M = " P M " ; 
 	 	 	 i f   ( ( t h i s . H o u r s - 1 2 ) < 1 0 ) 
 	 	 	 	 f i n a l H o u r = " 0 " + ( ( p a r s e I n t ( t h i s . H o u r s , 1 0 ) ) - 1 2 ) ; 
 	 	 	 e l s e 
 	 	 	 	 f i n a l H o u r = p a r s e I n t ( t h i s . H o u r s , 1 0 ) - 1 2 ; 	 
 	 	 } 
 	 	 e l s e   { 
 	 	 	 t h i s . A M o r P M = " A M " ; 
 	 	 	 i f   ( t h i s . H o u r s < 1 0 ) 
 	 	 	 	 f i n a l H o u r = " 0 " + p a r s e I n t ( t h i s . H o u r s , 1 0 ) ; 
 	 	 	 e l s e 
 	 	 	 	 f i n a l H o u r = t h i s . H o u r s ; 	 
 	 	 } 
 	 } 
 	 e l s e   i f   ( T i m e M o d e = = 2 4 )   { 
 	 	 i f   ( t h i s . H o u r s < 1 0 ) 
 	 	 	 f i n a l H o u r = " 0 " + p a r s e I n t ( t h i s . H o u r s , 1 0 ) ; 
 	 	 e l s e 	 
 	 	 	 f i n a l H o u r = t h i s . H o u r s ; 
 	 } 
 	 r e t u r n   f i n a l H o u r ; 
 } 	 	 	 	 
 C a l e n d a r . p r o t o t y p e . g e t S h o w H o u r = g e t S h o w H o u r ; 	 	 
 
 f u n c t i o n   g e t S h o w A M o r P M ( )   { 
 	 r e t u r n   t h i s . A M o r P M ; 	 
 } 	 	 	 	 
 C a l e n d a r . p r o t o t y p e . g e t S h o w A M o r P M = g e t S h o w A M o r P M ; 	 	 
 
 f u n c t i o n   G e t M o n t h N a m e ( I s L o n g )   { 
 	 v a r   M o n t h = M o n t h N a m e [ t h i s . M o n t h ] ; 
 	 i f   ( I s L o n g ) 
 	 	 r e t u r n   M o n t h ; 
 	 e l s e 
 	 	 r e t u r n   M o n t h . s u b s t r ( 0 , 3 ) ; 
 } 
 C a l e n d a r . p r o t o t y p e . G e t M o n t h N a m e = G e t M o n t h N a m e ; 
 
 f u n c t i o n   G e t M o n D a y s ( )   {   / / G e t   n u m b e r   o f   d a y s   i n   a   m o n t h 
 	 v a r   D a y s I n M o n t h = [ 3 1 ,   2 8 ,   3 1 ,   3 0 ,   3 1 ,   3 0 ,   3 1 ,   3 1 ,   3 0 ,   3 1 ,   3 0 ,   3 1 ] ; 
 	 i f   ( t h i s . I s L e a p Y e a r ( ) )   { 
 	 	 D a y s I n M o n t h [ 1 ] = 2 9 ; 
 	 } 	 
 	 r e t u r n   D a y s I n M o n t h [ t h i s . M o n t h ] ; 	 
 } 
 C a l e n d a r . p r o t o t y p e . G e t M o n D a y s = G e t M o n D a y s ; 
 
 f u n c t i o n   I s L e a p Y e a r ( )   { 
 	 i f   ( ( t h i s . Y e a r % 4 ) = = 0 )   { 
 	 	 i f   ( ( t h i s . Y e a r % 1 0 0 = = 0 )   & &   ( t h i s . Y e a r % 4 0 0 ) ! = 0 )   { 
 	 	 	 r e t u r n   f a l s e ; 
 	 	 } 
 	 	 e l s e   { 
 	 	 	 r e t u r n   t r u e ; 
 	 	 } 
 	 } 
 	 e l s e   { 
 	 	 r e t u r n   f a l s e ; 
 	 } 
 } 
 C a l e n d a r . p r o t o t y p e . I s L e a p Y e a r = I s L e a p Y e a r ; 
 
 f u n c t i o n   F o r m a t D a t e ( p D a t e ) 
 { 
 	 v a r   M o n t h D i g i t = t h i s . M o n t h + 1 ; 
 	 i f   ( P r e c e d e Z e r o = = t r u e )   { 
 	 	 i f   ( p D a t e < 1 0 ) 
 	 	 	 p D a t e = " 0 " + p D a t e ; 
 	 	 i f   ( M o n t h D i g i t < 1 0 ) 
 	 	 	 M o n t h D i g i t = " 0 " + M o n t h D i g i t ; 
 	 } 
 
 	 i f   ( t h i s . F o r m a t . t o U p p e r C a s e ( ) = = " D D M M Y Y Y Y " ) 
 	 	 r e t u r n   ( p D a t e + D a t e S e p a r a t o r + M o n t h D i g i t + D a t e S e p a r a t o r + t h i s . Y e a r ) ; 
 	 e l s e   i f   ( t h i s . F o r m a t . t o U p p e r C a s e ( ) = = " D D M M M Y Y Y Y " ) 
 	 	 r e t u r n   ( p D a t e + D a t e S e p a r a t o r + t h i s . G e t M o n t h N a m e ( f a l s e ) + D a t e S e p a r a t o r + t h i s . Y e a r ) ; 
 	 e l s e   i f   ( t h i s . F o r m a t . t o U p p e r C a s e ( ) = = " M M D D Y Y Y Y " ) 
 	 	 r e t u r n   ( M o n t h D i g i t + D a t e S e p a r a t o r + p D a t e + D a t e S e p a r a t o r + t h i s . Y e a r ) ; 
 	 e l s e   i f   ( t h i s . F o r m a t . t o U p p e r C a s e ( ) = = " M M M D D Y Y Y Y " ) 
 	 	 r e t u r n   ( t h i s . G e t M o n t h N a m e ( f a l s e ) + D a t e S e p a r a t o r + p D a t e + D a t e S e p a r a t o r + t h i s . Y e a r ) ; 
 	 e l s e   i f   ( t h i s . F o r m a t . t o U p p e r C a s e ( ) = = " Y Y Y Y M M D D " ) 
 	 	 r e t u r n   ( t h i s . Y e a r + D a t e S e p a r a t o r + M o n t h D i g i t + D a t e S e p a r a t o r + p D a t e ) ; 
 	 e l s e   i f   ( t h i s . F o r m a t . t o U p p e r C a s e ( ) = = " Y Y Y Y M M M D D " ) 
 	 	 r e t u r n   ( t h i s . Y e a r + D a t e S e p a r a t o r + t h i s . G e t M o n t h N a m e ( f a l s e ) + D a t e S e p a r a t o r + p D a t e ) ; 	 
 	 e l s e 	 	 	 	 	 
 	 	 r e t u r n   ( p D a t e + D a t e S e p a r a t o r + ( t h i s . M o n t h + 1 ) + D a t e S e p a r a t o r + t h i s . Y e a r ) ; 
 } 
 C a l e n d a r . p r o t o t y p e . F o r m a t D a t e = F o r m a t D a t e ; 
 	 
 f u n c t i o n   c l o s e w i n ( i d )   { 
       v a r   C a l I d   =   d o c u m e n t . g e t E l e m e n t B y I d ( i d ) ; 
       C a l I d . f o c u s ( ) ; 
       w i n C a l . s t y l e . v i s i b i l i t y = ' h i d d e n ' ; 
   } 
 
 f u n c t i o n   c h a n g e B o r d e r ( e l e m e n t ,   c o l )   { 
     i f   ( c o l   = =   0 )   { 
         e l e m e n t . s t y l e . b o r d e r C o l o r   =   " b l a c k " ; 
         e l e m e n t . s t y l e . c u r s o r   =   " p o i n t e r " ; 
     } 
     e l s e   { 
         e l e m e n t . s t y l e . b o r d e r C o l o r   =   " w h i t e " ; 
         e l e m e n t . s t y l e . c u r s o r   =   " a u t o " ; 
     } 
 } 
 
 f u n c t i o n   p i c k I t ( e v t )   { 
       v a r   I E   =   d o c u m e n t . a l l ? t r u e : f a l s e 
         
       / /   a c c e s s e s   t h e   e l e m e n t   t h a t   g e n e r a t e s   t h e   e v e n t   a n d   r e t r i e v e s   i t s   I D 
       i f   ( ! I E )   {   / /   w 3 c 
 	     v a r   o b j e c t I D   =   e v t . t a r g e t . i d ; 
             i f   ( o b j e c t I D . i n d e x O f ( c a l S p a n I D )   ! =   - 1 ) { 
                   v a r   d o m   =   d o c u m e n t . g e t E l e m e n t B y I d ( o b j e c t I D ) ; 
                   c n L e f t = e v t . p a g e X ; 
                   c n T o p = e v t . p a g e Y ; 
 
                   i f   ( d o m . o f f s e t L e f t ) { 
                       c n L e f t   =   ( c n L e f t   -   d o m . o f f s e t L e f t ) ;   c n T o p   =   ( c n T o p   -   d o m . o f f s e t T o p ) ; 
                     } 
               } 
 	     / /   g e t   m o u s e   p o s i t i o n   o n   c l i c k 
 	     x p o s   =   ( e v t . p a g e X ) ; 
 	     y p o s   =   ( e v t . p a g e Y ) ; 
 	 }       
       e l s e   {   / /   I E 
 	     v a r   o b j e c t I D   =   e v e n t . s r c E l e m e n t . i d ; 
             c n L e f t = e v e n t . o f f s e t X ; 
             c n T o p = ( e v e n t . o f f s e t Y ) ; 
 	     / /   g e t   m o u s e   p o s i t i o n   o n   c l i c k 
 	     v a r   d e   =   d o c u m e n t . d o c u m e n t E l e m e n t ; 
             v a r   b   =   d o c u m e n t . b o d y ; 
             x p o s   =   e v e n t . c l i e n t X   +   ( d e . s c r o l l L e f t   | |   b . s c r o l l L e f t )   -   ( d e . c l i e n t L e f t   | |   0 ) ; 
             y p o s   =   e v e n t . c l i e n t Y   +   ( d e . s c r o l l T o p   | |   b . s c r o l l T o p )   -   ( d e . c l i e n t T o p   | |   0 ) ; 
         } 
       / /   v e r i f y   i f   t h i s   i s   a   v a l i d   e l e m e n t   t o   p i c k     
       i f   ( o b j e c t I D . i n d e x O f ( c a l S p a n I D )   ! =   - 1 ) { 
             d o m S t y l e   =   d o c u m e n t . g e t E l e m e n t B y I d ( o b j e c t I D ) . s t y l e ; 
         } 
       i f   ( d o m S t y l e )   {   
             d o m S t y l e . z I n d e x   =   1 0 0 ; 
             r e t u r n   f a l s e ; 
         } 
       e l s e   { 
             d o m S t y l e   =   n u l l ; 
             r e t u r n ; 
         } 
   } 
 
 f u n c t i o n   d r a g I t ( e v t )   { 
       i f   ( d o m S t y l e )   { 
             i f   ( w i n d o w . E v e n t )   { 
                   d o m S t y l e . l e f t   =   ( e v t . c l i e n t X - c n L e f t   +   d o c u m e n t . b o d y . s c r o l l L e f t ) + ' p x ' ; 
                   d o m S t y l e . t o p   =   ( e v t . c l i e n t Y - c n T o p   +   d o c u m e n t . b o d y . s c r o l l T o p ) + ' p x ' ; 
               }   
             e l s e   { 
                   d o m S t y l e . l e f t   =   ( e v e n t . c l i e n t X - c n L e f t   +   d o c u m e n t . b o d y . s c r o l l L e f t ) + ' p x ' ;   
                   d o m S t y l e . t o p   =   ( e v e n t . c l i e n t Y - c n T o p   +   d o c u m e n t . b o d y . s c r o l l T o p ) + ' p x ' ; 
               } 
         }   
   } 
 
 f u n c t i o n   d r o p I t ( )   { 
       i f   ( d o m S t y l e )   {   
             d o m S t y l e . z I n d e x   =   0 ; 
             d o m S t y l e   =   n u l l ; 
         } 
   } 