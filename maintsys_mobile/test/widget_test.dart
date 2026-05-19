import 'package:flutter_test/flutter_test.dart';
import 'package:maintsys_mobile/main.dart';

void main() {
  testWidgets('App renderiza sem erros', (WidgetTester tester) async {
    await tester.pumpWidget(const MaintSysApp());
    expect(find.text('MaintSys'), findsAny);
  });
}
